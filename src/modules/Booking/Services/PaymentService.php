 <?php

namespace Modules\Booking\Services;

use Illuminate\Support\Facades\DB;
use Modules\Booking\Contracts\PaymentGatewayInterface;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\Payment;
use Throwable;

readonly class PaymentService
{
    public function __construct(
        private PaymentGatewayInterface  $gateway,
        private BookingCollectionService $collectionService,
    ) {
    }

    public function poll(Payment $payment): void
    {
        if ($payment->status !== Payment::STATUS_PROCESSING) {
            return;
        }

        if ($payment->expires_at?->isPast()) {
            try {
                if ($payment->invoice_id) {
                    $this->gateway->revokeInvoice((string) $payment->invoice_id);
                }
            } catch (Throwable $exception) {
                $payment->logs = ['error' => mb_substr($exception->getMessage(), 0, 1000)];
            }

            $payment->update([
                'status' => Payment::STATUS_EXPIRED,
                'last_checked_at' => now(),
                'next_check_at' => null,
            ]);

            return;
        }

        try {
            $result = $this->gateway->getInvoiceStatus((string) $payment->invoice_id);
            $payment->logs = $result['payload'];
            $payment->last_checked_at = now();

            match ($result['status']) {
                Payment::STATUS_PAID => $this->completeAfterSavingProviderResponse($payment),
                Payment::STATUS_FAILED => $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'logs' => $result['payload'],
                    'last_checked_at' => now(),
                    'next_check_at' => null,
                ]),
                Payment::STATUS_EXPIRED => $payment->update([
                    'status' => Payment::STATUS_EXPIRED,
                    'logs' => $result['payload'],
                    'last_checked_at' => now(),
                    'next_check_at' => null,
                ]),
                default => $this->scheduleNextCheck($payment),
            };
        } catch (Throwable $exception) {
            $payment->logs = ['error' => mb_substr($exception->getMessage(), 0, 1000)];
            $this->scheduleNextCheck($payment);
        }
    }

    private function completeAfterSavingProviderResponse(Payment $payment): void
    {
        $payment->save();
        $this->complete($payment);
    }

    public function complete(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $lockedPayment = Payment::query()->lockForUpdate()->find($payment->id);

            if (!$lockedPayment || $lockedPayment->status !== Payment::STATUS_PROCESSING) {
                return;
            }

            $booking = Booking::query()->lockForUpdate()->find($lockedPayment->booking_id);

            if (!$booking) {
                return;
            }

            $invitation = $booking->invitationForUser((int) $lockedPayment->create_user, true);

            if (!$invitation || !$lockedPayment->transitionToPaid()) {
                return;
            }

            $invitation->update([
                'prepayment_paid' => true,
                'prepayment_paid_status' => BookingHunterInvitation::PREPAYMENT_PAID,
            ]);

            if ($booking->status !== Booking::PREPAYMENT_COLLECTION) {
                return;
            }

            $accepted = $booking->countAcceptedHunters();
            $paid = $booking->countAcceptedAndPaidHunters();

            if ($accepted > 0 && $paid >= $accepted) {
                $booking->prepayment_paid = true;
                $booking->save();
                $this->collectionService->startBedTimer($booking);
            }
        }, 3);
    }

    private function scheduleNextCheck(Payment $payment): void
    {
        $attempt = $payment->attempts + 1;
        $delays = (array) config('paykeeper.retry_delays', [60, 120, 300, 600]);
        $delay = (int) ($delays[min($attempt - 1, count($delays) - 1)] ?? 900);

        $payment->attempts = $attempt;
        $payment->next_check_at = now()->addSeconds($delay);
        $payment->save();
    }
}
