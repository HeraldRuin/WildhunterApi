<?php

namespace Tests\Feature\Booking;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Contracts\PaymentGatewayInterface;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\Payment;
use Modules\Booking\Services\PaymentService;
use RuntimeException;
use Tests\Support\FakePaymentGateway;
use Tests\Support\PreparesAuthDatabase;
use Tests\Support\PreparesPaymentDatabase;
use Tests\Support\SeedsPrepayment;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use PreparesAuthDatabase;
    use PreparesPaymentDatabase;
    use SeedsPrepayment;

    private FakePaymentGateway $gateway;

    private PaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareAuthDatabase();
        $this->preparePaymentDatabase();

        config([
            'paykeeper.retry_delays' => [60, 120, 300, 600, 900],
            'paykeeper.currency' => 'RUB',
        ]);

        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGatewayInterface::class, $this->gateway);
        $this->payments = $this->app->make(PaymentService::class);
    }

    protected function tearDown(): void
    {
        $this->travelBack();

        parent::tearDown();
    }

    public function test_poll_expires_invoice_and_revokes_it(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter]);
        $payment = $this->makeProcessingPayment($bookingId, $hunter, [
            'expires_at' => now()->subMinute(),
        ]);

        $this->payments->poll($payment);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_EXPIRED, $payment->status);
        $this->assertNull($payment->next_check_at);
        $this->assertNotNull($payment->last_checked_at);
        $this->assertSame(['inv-'.$hunter->id], $this->gateway->revoked);
        $this->assertSame([], $this->gateway->statusChecks);
        $this->assertSame(Booking::PREPAYMENT_COLLECTION, Booking::query()->find($bookingId)->status);
    }

    public function test_poll_expires_invoice_when_revoke_fails(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter]);
        $payment = $this->makeProcessingPayment($bookingId, $hunter, [
            'expires_at' => now()->subMinute(),
        ]);
        $this->gateway->revokeException = new RuntimeException('revoke down');

        $this->payments->poll($payment);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_EXPIRED, $payment->status);
        $this->assertNull($payment->next_check_at);
        $this->assertSame(['error' => 'revoke down'], $payment->logs);
        $this->assertSame([], $this->gateway->revoked);
    }

    public function test_poll_marks_payment_failed_and_leaves_booking(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter]);
        $payment = $this->makeProcessingPayment($bookingId, $hunter);
        $this->gateway->statusResult = [
            'status' => Payment::STATUS_FAILED,
            'payload' => ['state' => 'rejected'],
        ];

        $this->payments->poll($payment);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_FAILED, $payment->status);
        $this->assertNull($payment->next_check_at);
        $this->assertSame(['state' => 'rejected'], $payment->logs);
        $this->assertSame(Booking::PREPAYMENT_COLLECTION, Booking::query()->find($bookingId)->status);
        $this->assertSame(0, DB::table('bc_booking_meta')->count());
    }

    public function test_poll_marks_payment_expired_from_provider_status(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter]);
        $payment = $this->makeProcessingPayment($bookingId, $hunter);
        $this->gateway->statusResult = [
            'status' => Payment::STATUS_EXPIRED,
            'payload' => ['state' => 'expired'],
        ];

        $this->payments->poll($payment);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_EXPIRED, $payment->status);
        $this->assertNull($payment->next_check_at);
        $this->assertSame([], $this->gateway->revoked);
    }

    public function test_poll_schedules_retry_while_invoice_is_still_processing(): void
    {
        $this->freezeSecond();

        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter]);
        $payment = $this->makeProcessingPayment($bookingId, $hunter);
        $this->gateway->statusResult = [
            'status' => Payment::STATUS_PROCESSING,
            'payload' => ['state' => 'pending'],
        ];

        $this->payments->poll($payment);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PROCESSING, $payment->status);
        $this->assertSame(1, $payment->attempts);
        $this->assertSame(
            now()->addSeconds(60)->toDateTimeString(),
            $payment->next_check_at?->toDateTimeString(),
        );

        $this->payments->poll($payment->fresh());

        $payment->refresh();
        $this->assertSame(2, $payment->attempts);
        $this->assertSame(
            now()->addSeconds(120)->toDateTimeString(),
            $payment->next_check_at?->toDateTimeString(),
        );
        $this->assertSame(Booking::PREPAYMENT_COLLECTION, Booking::query()->find($bookingId)->status);
    }

    public function test_poll_schedules_retry_when_gateway_throws(): void
    {
        $this->freezeSecond();

        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter]);
        $payment = $this->makeProcessingPayment($bookingId, $hunter);
        $this->gateway->statusException = new RuntimeException('gateway timeout');

        $this->payments->poll($payment);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PROCESSING, $payment->status);
        $this->assertSame(1, $payment->attempts);
        $this->assertSame(['error' => 'gateway timeout'], $payment->logs);
        $this->assertSame(
            now()->addSeconds(60)->toDateTimeString(),
            $payment->next_check_at?->toDateTimeString(),
        );
    }

    public function test_booking_advances_only_when_every_accepted_hunter_has_paid(): void
    {
        $first = User::factory()->create(['email' => 'first@example.com']);
        $second = User::factory()->create(['email' => 'second@example.com']);
        $bookingId = $this->seedPrepaymentBooking([$first, $second], 1000);
        $firstPayment = $this->makeProcessingPayment($bookingId, $first);
        $secondPayment = $this->makeProcessingPayment($bookingId, $second);
        $this->gateway->statusResult = [
            'status' => Payment::STATUS_PAID,
            'payload' => ['state' => 'paid'],
        ];

        $this->payments->poll($firstPayment);

        $firstPayment->refresh();
        $booking = Booking::query()->find($bookingId);
        $this->assertSame(Payment::STATUS_PAID, $firstPayment->status);
        $this->assertSame(Booking::PREPAYMENT_COLLECTION, $booking->status);
        $this->assertSame(0, (int) $booking->prepayment_paid);
        $this->assertSame(0, DB::table('bc_booking_meta')->count());
        $this->assertHunterPaid($bookingId, $first->id);
        $this->assertHunterUnpaid($bookingId, $second->id);

        $this->payments->poll($secondPayment);

        $secondPayment->refresh();
        $booking->refresh();
        $this->assertSame(Payment::STATUS_PAID, $secondPayment->status);
        $this->assertSame(Booking::BED_COLLECTION, $booking->status);
        $this->assertSame(1, (int) $booking->prepayment_paid);
        $this->assertHunterPaid($bookingId, $second->id);
        $this->assertSame(
            ['beds_end_at', 'beds_start_at', 'beds_timer_hours'],
            DB::table('bc_booking_meta')->where('booking_id', $bookingId)->orderBy('name')->pluck('name')->all(),
        );
    }

    public function test_second_complete_does_not_move_booking_again(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter]);
        $payment = $this->makeProcessingPayment($bookingId, $hunter);

        $this->payments->complete($payment);

        $booking = Booking::query()->find($bookingId);
        $this->assertSame(Booking::BED_COLLECTION, $booking->status);
        $startedAt = DB::table('bc_booking_meta')
            ->where('booking_id', $bookingId)
            ->where('name', 'beds_start_at')
            ->value('val');

        DB::table('bc_booking_meta')
            ->where('booking_id', $bookingId)
            ->where('name', 'beds_start_at')
            ->update(['val' => 'sentinel']);

        $this->payments->complete($payment->fresh());

        $booking->refresh();
        $this->assertSame(Booking::BED_COLLECTION, $booking->status);
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        $this->assertSame(
            'sentinel',
            DB::table('bc_booking_meta')->where('booking_id', $bookingId)->where('name', 'beds_start_at')->value('val'),
        );
        $this->assertNotSame('sentinel', $startedAt);
        $this->assertSame(3, DB::table('bc_booking_meta')->where('booking_id', $bookingId)->count());
    }

    public function test_reconcile_starts_bed_collection_when_all_accepted_hunters_are_already_paid(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter]);
        DB::table('bc_booking_hunter_invitations')
            ->where('hunter_id', $hunter->id)
            ->update([
                'prepayment_paid' => 1,
                'prepayment_paid_status' => BookingHunterInvitation::PREPAYMENT_PAID,
            ]);

        $this->payments->reconcilePrepaymentCollections();

        $booking = Booking::query()->find($bookingId);
        $this->assertSame(Booking::BED_COLLECTION, $booking->status);
        $this->assertSame(1, (int) $booking->prepayment_paid);
    }

    private function assertHunterPaid(int $bookingId, int $hunterId): void
    {
        $invitation = $this->invitation($bookingId, $hunterId);
        $this->assertTrue((bool) $invitation->prepayment_paid);
        $this->assertSame(BookingHunterInvitation::PREPAYMENT_PAID, $invitation->prepayment_paid_status);
    }

    private function assertHunterUnpaid(int $bookingId, int $hunterId): void
    {
        $invitation = $this->invitation($bookingId, $hunterId);
        $this->assertFalse((bool) $invitation->prepayment_paid);
        $this->assertSame(BookingHunterInvitation::PREPAYMENT_PENDING, $invitation->prepayment_paid_status);
    }

    private function invitation(int $bookingId, int $hunterId): BookingHunterInvitation
    {
        $masterId = DB::table('bc_booking_hunters')->where('booking_id', $bookingId)->value('id');

        return BookingHunterInvitation::query()
            ->where('booking_hunter_id', $masterId)
            ->where('hunter_id', $hunterId)
            ->firstOrFail();
    }
}
