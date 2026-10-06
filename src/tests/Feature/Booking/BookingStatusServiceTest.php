<?php

namespace Tests\Feature\Booking;

use App\Exceptions\ConflictException;
use Modules\Booking\Models\Booking;
use Modules\Booking\Services\BookingStatusService;
use Modules\Role\Models\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingStatusServiceTest extends TestCase
{
    private BookingStatusService $statuses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statuses = new BookingStatusService();
    }

    public function test_hunter_may_set_only_invitation(): void
    {
        $this->assertSame(
            [Booking::INVITATION],
            $this->statuses->getAllowedStatuses(Role::CUSTOMER),
        );
    }

    public function test_base_admin_may_set_only_completed(): void
    {
        $this->assertSame(
            [Booking::COMPLETED],
            $this->statuses->getAllowedStatuses(Role::ADMIN),
        );
    }

    public function test_unknown_role_may_set_every_configured_status(): void
    {
        $this->assertSame([
            Booking::COMPLETED,
            Booking::PROCESSING,
            Booking::CONFIRMED,
            Booking::CANCELLED,
            Booking::PAID,
            Booking::UNPAID,
            Booking::PARTIAL_PAYMENT,
            Booking::START_COLLECTION,
            Booking::INVITATION,
            Booking::PREPAYMENT_COLLECTION,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
        ], $this->statuses->getAllowedStatuses('guest'));
    }

    public function test_dropdown_statuses_keep_a_fixed_order(): void
    {
        $this->assertSame([
            Booking::CANCELLED,
            Booking::PROCESSING,
            Booking::CONFIRMED,
            Booking::START_COLLECTION,
            Booking::FINISHED_COLLECTION,
            Booking::PREPAYMENT_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
            Booking::PAID,
        ], $this->statuses->getDropdownStatuses());
    }

    #[DataProvider('lockedStatuses')]
    public function test_cancelled_and_completed_are_locked(string $status): void
    {
        $booking = new Booking();
        $booking->status = $status;

        try {
            $this->statuses->canChangeBookingState($booking);
            $this->fail('Статус '.$status.' должен быть заблокирован');
        } catch (ConflictException $exception) {
            $this->assertSame('booking_status_locked', $exception->getErrorCode());
            $this->assertSame('booking', $exception->getDomain());
            $this->assertSame(['status' => $status], $exception->getContext());
        }
    }

    #[DataProvider('openStatuses')]
    public function test_other_statuses_can_be_changed(string $status): void
    {
        $booking = new Booking();
        $booking->status = $status;

        $this->statuses->canChangeBookingState($booking);

        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function lockedStatuses(): array
    {
        return [
            'cancelled' => [Booking::CANCELLED],
            'completed' => [Booking::COMPLETED],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function openStatuses(): array
    {
        return [
            'processing' => [Booking::PROCESSING],
            'confirmed' => [Booking::CONFIRMED],
            'paid' => [Booking::PAID],
            'collection' => [Booking::START_COLLECTION],
            'prepayment_collection' => [Booking::PREPAYMENT_COLLECTION],
            'finished_collection' => [Booking::FINISHED_COLLECTION],
        ];
    }
}
