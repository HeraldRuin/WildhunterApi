<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Contracts\PaymentGatewayInterface;
use Modules\Booking\Dto\PaykeeperOrderDTO;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\Payment;

class PaymentManagerService
{
    public function __construct(private readonly PaymentGatewayInterface $gateway)
    {
    }

    /**
     * @return array{payment_url: string, status: string, expires_at: ?string}
     */
    public function createPayment(string $bookingCode, User|int $user): array
    {
        $user = $this->resolveUser($user);

        return DB::transaction(function () use ($bookingCode, $user): array {
            [$booking, $invitation, $expiresAt] = $this->validateContext($bookingCode, $user, true);

            $existing = Payment::query()
                ->where('booking_id', $booking->id)
                ->forUser($user->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($existing?->status === Payment::STATUS_PAID) {
                throw new ConflictException(errorCode: 'payment_already_paid', domain: 'payment');
            }

            if ($existing?->isReusable()) {
                return $this->data($existing);
            }

            if ($existing?->status === Payment::STATUS_PROCESSING) {
                if ($existing->invoice_id) {
                    $this->gateway->revokeInvoice((string) $existing->invoice_id);
                }

                $existing->update([
                    'status' => Payment::STATUS_EXPIRED,
                    'next_check_at' => null,
                ]);
            }

            $amount = round((float) $booking->total / max(1, $booking->countAcceptedHunters()), 2);
            $providerExpiry = now()->addMinutes((int) config('paykeeper.invoice_ttl_minutes', 30));
            $paymentExpiry = $expiresAt->lessThan($providerExpiry) ? $expiresAt : $providerExpiry;
            $payment = Payment::query()->create([
                'booking_id' => $booking->id,
                'object_id' => $booking->object_id,
                'object_model' => $booking->type,
                'user_id' => $user->id,
                'payment_gateway' => 'paykeeper',
                'status' => Payment::STATUS_PROCESSING,
                'amount' => $amount,
                'currency' => (string) config('paykeeper.currency', 'RUB'),
                'expires_at' => $paymentExpiry,
                'next_check_at' => now()->addMinute(),
                'attempts' => 0,
                'create_user' => $user->id,
            ]);

            $invoice = $this->gateway->createInvoice(new PaykeeperOrderDTO(
                orderId: $payment->code,
                amount: $amount,
                customerName: trim((string) ($user->name ?? $user->getAttribute('first_name') ?? $user->email)),
                email: $user->email,
                phone: $user->getAttribute('phone'),
                description: 'Prepayment for booking '.$booking->code,
                currency: (string) config('paykeeper.currency', 'RUB'),
                expiresAt: $paymentExpiry->format('Y-m-d H:i:s'),
            ));

            $payment->update([
                'invoice_id' => $invoice['external_id'],
                'payment_url' => $invoice['payment_url'],
                'logs' => $invoice['payload'],
            ]);

            return $this->data($payment);
        }, 3);
    }

    /**
     * @return array{payment_url: ?string, status: string, expires_at: ?string}
     */
    public function getPaymentStatus(string $bookingCode, User|int $user): array
    {
        $user = $this->resolveUser($user);
        $booking = Booking::query()->where('code', $bookingCode)->first();

        if (!$booking) {
            throw new NotFoundException(errorCode: 'booking_not_found', domain: 'booking');
        }

        $invitation = $booking->invitationForUser($user->id);

        if (!$invitation || $invitation->status !== BookingHunterInvitation::STATUS_ACCEPTED) {
            throw new ForbiddenException(errorCode: 'prepayment_invitation_not_accepted', domain: 'payment');
        }

        $payment = Payment::query()
            ->where('booking_id', $booking->id)
            ->forUser($user->id)
            ->latest('id')
            ->first();

        if (!$payment) {
            throw new NotFoundException(errorCode: 'payment_not_found', domain: 'payment');
        }

        return $this->data($payment);
    }

    /**
     * @return array{Booking, BookingHunterInvitation, Carbon}
     */
    private function validateContext(string $bookingCode, User $user, bool $forUpdate): array
    {
        $query = Booking::query()->where('code', $bookingCode);
        $booking = ($forUpdate ? $query->lockForUpdate() : $query)->first();

        if (!$booking) {
            throw new NotFoundException(errorCode: 'booking_not_found', domain: 'booking');
        }

        if ($booking->status !== Booking::PREPAYMENT_COLLECTION) {
            throw new ConflictException(errorCode: 'booking_prepayment_collection_not_active', domain: 'payment');
        }

        $invitation = $booking->invitationForUser($user->id, $forUpdate);

        if (!$invitation || $invitation->status !== BookingHunterInvitation::STATUS_ACCEPTED) {
            throw new ForbiddenException(errorCode: 'prepayment_invitation_not_accepted', domain: 'payment');
        }

        if ($invitation->prepayment_paid
            || $invitation->prepayment_paid_status === BookingHunterInvitation::PREPAYMENT_PAID) {
            throw new ConflictException(errorCode: 'payment_already_paid', domain: 'payment');
        }

        if ($invitation->prepayment_paid_status === BookingHunterInvitation::PREPAYMENT_UNPAID) {
            throw new ConflictException(errorCode: 'prepayment_marked_unpaid', domain: 'payment');
        }

        $endAt = $booking->getMeta('paid_end_at');

        if (!$endAt) {
            throw new ConflictException(errorCode: 'prepayment_timer_not_found', domain: 'payment');
        }

        if (($expiresAt = Carbon::parse($endAt))->isPast()) {
            throw new ConflictException(errorCode: 'prepayment_timer_expired', domain: 'payment');
        }

        return [$booking, $invitation, $expiresAt];
    }

    private function resolveUser(User|int $user): User
    {
        if ($user instanceof User) {
            return $user;
        }

        return User::query()->find($user)
            ?? throw new NotFoundException(errorCode: 'user_not_found', domain: 'user');
    }

    private function data(Payment $payment): array
    {
        return [
            'payment_url' => $payment->payment_url,
            'status' => $payment->status,
            'expires_at' => $payment->expires_at?->toIso8601String(),
        ];
    }
}
