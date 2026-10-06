<?php

namespace Tests\Feature\Booking;

use App\Exceptions\BaseException;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Contracts\PaymentGatewayInterface;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\Payment;
use Modules\Booking\Services\PaymentManagerService;
use Tests\Support\FakePaymentGateway;
use Tests\Support\PreparesAuthDatabase;
use Tests\Support\PreparesPaymentDatabase;
use Tests\Support\SeedsPrepayment;
use Tests\TestCase;

class PaymentManagerServiceTest extends TestCase
{
    use PreparesAuthDatabase;
    use PreparesPaymentDatabase;
    use SeedsPrepayment;

    private FakePaymentGateway $gateway;

    private PaymentManagerService $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareAuthDatabase();
        $this->preparePaymentDatabase();

        config([
            'paykeeper.currency' => 'RUB',
            'paykeeper.invoice_ttl_minutes' => 30,
        ]);

        $this->gateway = new FakePaymentGateway();
        $this->app->instance(PaymentGatewayInterface::class, $this->gateway);
        $this->manager = $this->app->make(PaymentManagerService::class);
    }

    public function test_create_payment_splits_amount_and_stores_invoice(): void
    {
        $first = User::factory()->create([
            'email' => 'first@example.com',
            'name' => 'Иван Петров',
        ]);
        $second = User::factory()->create(['email' => 'second@example.com']);
        $this->seedPrepaymentBooking([$first, $second], 1000, 'BK-PAY');

        $result = $this->manager->createPayment('BK-PAY', $first);

        $payment = Payment::query()->where('create_user', $first->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame(Payment::STATUS_PROCESSING, $result['status']);
        $this->assertSame('https://pay.example/inv-1', $result['payment_url']);
        $this->assertSame('500.00', $payment->amount);
        $this->assertSame('inv-1', $payment->invoice_id);
        $this->assertSame('RUB', $payment->currency);
        $this->assertNotNull($payment->expires_at);
        $this->assertCount(1, $this->gateway->invoices);
        $this->assertSame(500.0, $this->gateway->invoices[0]->amount);
        $this->assertSame('first@example.com', $this->gateway->invoices[0]->email);
        $this->assertSame('Иван Петров', $this->gateway->invoices[0]->customerName);
    }

    public function test_create_payment_reuses_active_invoice(): void
    {
        $hunter = User::factory()->create();
        $this->seedPrepaymentBooking([$hunter], 1000, 'BK-PAY');

        $first = $this->manager->createPayment('BK-PAY', $hunter);
        $second = $this->manager->createPayment('BK-PAY', $hunter);

        $this->assertSame($first['payment_url'], $second['payment_url']);
        $this->assertSame(Payment::STATUS_PROCESSING, $second['status']);
        $this->assertCount(1, $this->gateway->invoices);
        $this->assertSame(1, Payment::query()->count());
        $this->assertSame([], $this->gateway->revoked);
    }

    public function test_create_payment_revokes_expired_processing_invoice_and_creates_new_one(): void
    {
        $hunter = User::factory()->create();
        $this->seedPrepaymentBooking([$hunter], 800, 'BK-PAY');

        $this->manager->createPayment('BK-PAY', $hunter);
        $existing = Payment::query()->first();
        $existing->update(['expires_at' => now()->subMinute()]);

        $result = $this->manager->createPayment('BK-PAY', $hunter);

        $existing->refresh();
        $latest = Payment::query()->latest('id')->first();
        $this->assertSame(Payment::STATUS_EXPIRED, $existing->status);
        $this->assertNull($existing->next_check_at);
        $this->assertSame(['inv-1'], $this->gateway->revoked);
        $this->assertSame(Payment::STATUS_PROCESSING, $result['status']);
        $this->assertSame('https://pay.example/inv-2', $result['payment_url']);
        $this->assertSame('inv-2', $latest->invoice_id);
        $this->assertSame('800.00', $latest->amount);
        $this->assertCount(2, $this->gateway->invoices);
    }

    public function test_create_payment_rejects_already_paid_invoice(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter], 1000, 'BK-PAY');
        $this->makeProcessingPayment($bookingId, $hunter, [
            'status' => Payment::STATUS_PAID,
        ]);

        $this->assertExceptionCode(ConflictException::class, 'payment_already_paid', function () use ($hunter): void {
            $this->manager->createPayment('BK-PAY', $hunter);
        });

        $this->assertSame([], $this->gateway->invoices);
        $this->assertSame(1, Payment::query()->count());
    }

    public function test_create_payment_rejects_booking_outside_prepayment_collection(): void
    {
        $hunter = User::factory()->create();
        $this->seedPrepaymentBooking([$hunter], 1000, 'BK-PAY', Booking::CONFIRMED);

        $this->assertExceptionCode(
            ConflictException::class,
            'booking_prepayment_collection_not_active',
            function () use ($hunter): void {
                $this->manager->createPayment('BK-PAY', $hunter);
            },
        );
    }

    public function test_get_payment_status_returns_latest_payment(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter], 1000, 'BK-PAY');
        $this->makeProcessingPayment($bookingId, $hunter, [
            'payment_url' => 'https://pay.example/current',
            'status' => Payment::STATUS_PROCESSING,
        ]);

        $result = $this->manager->getPaymentStatus('BK-PAY', $hunter);

        $this->assertSame('https://pay.example/current', $result['payment_url']);
        $this->assertSame(Payment::STATUS_PROCESSING, $result['status']);
        $this->assertNotNull($result['expires_at']);
    }

    public function test_get_payment_status_rejects_hunter_without_accepted_invitation(): void
    {
        $hunter = User::factory()->create();
        $bookingId = $this->seedPrepaymentBooking([$hunter], 1000, 'BK-PAY');
        DB::table('bc_booking_hunter_invitations')
            ->where('hunter_id', $hunter->id)
            ->update(['status' => BookingHunterInvitation::STATUS_PENDING]);
        $this->makeProcessingPayment($bookingId, $hunter);

        $this->assertExceptionCode(
            ForbiddenException::class,
            'prepayment_invitation_not_accepted',
            function () use ($hunter): void {
                $this->manager->getPaymentStatus('BK-PAY', $hunter);
            },
        );
    }

    private function assertExceptionCode(string $class, string $code, callable $callback): void
    {
        try {
            $callback();
            $this->fail('Ожидалось исключение '.$code.'.');
        } catch (BaseException $exception) {
            $this->assertInstanceOf($class, $exception);
            $this->assertSame($code, $exception->getErrorCode());
        }
    }
}
