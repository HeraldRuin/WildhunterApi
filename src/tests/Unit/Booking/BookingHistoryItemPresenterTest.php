<?php

namespace Tests\Unit\Booking;

use App\Models\User;
use Carbon\Carbon;
use Modules\Animals\Models\Animal;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;
use Modules\Booking\Services\BookingHistoryActionService;
use Modules\Booking\Services\BookingHistoryItemPresenter;
use Tests\TestCase;

class BookingHistoryItemPresenterTest extends TestCase
{
    public function test_hunting_total_includes_services_and_splits_per_hunter(): void
    {
        $booking = $this->booking(
            amountHunting: 120000,
            totalHunting: 4,
            durationDays: 3,
            services: [
                $this->service(AddetionalPrice::TROPHY, 1476),
                $this->service(AddetionalPrice::SPENDING, 5000),
            ],
        );

        $details = $this->present($booking);

        $this->assertSame(121476.0, $details['amount_hunting']);
        $this->assertSame(30369.0, $details['amount_hunting_per_person']);
        $this->assertSame(121476.0, $details['animal']['price_total']);
        $this->assertSame(30369.0, $details['animal']['price_per_person']);
        $this->assertSame(120000.0, (float) $booking->amount_hunting);
    }

    public function test_service_total_counts_food_by_days_and_skips_spendings(): void
    {
        $booking = $this->booking(
            amountHunting: 10000,
            totalHunting: 2,
            durationDays: 3,
            services: [
                $this->service(AddetionalPrice::PREPARATION, 300),
                $this->service(AddetionalPrice::FOOD, 100),
                $this->service(AddetionalPrice::ADDETIONAL, 50),
                $this->service(AddetionalPrice::SPENDING, 9999),
            ],
        );

        $details = $this->present($booking);

        $this->assertSame(10650.0, $details['amount_hunting']);
        $this->assertSame(5325.0, $details['amount_hunting_per_person']);
    }

    public function test_penalty_stays_in_full_on_the_charged_hunter(): void
    {
        $booking = $this->booking(
            amountHunting: 120000,
            totalHunting: 4,
            durationDays: 1,
            services: [
                $this->service(AddetionalPrice::PENALTY, 1000, 5),
            ],
        );

        $charged = $this->present($booking, 5);
        $other = $this->present($booking, 1);

        $this->assertSame(121000.0, $charged['amount_hunting']);
        $this->assertSame(121000.0, $other['amount_hunting']);
        $this->assertSame(31000.0, $charged['amount_hunting_per_person']);
        $this->assertSame(30000.0, $other['amount_hunting_per_person']);
    }

    public function test_hunting_amounts_stay_empty_without_organisation_price(): void
    {
        $booking = $this->booking(
            amountHunting: null,
            totalHunting: 4,
            durationDays: 1,
            services: [
                $this->service(AddetionalPrice::TROPHY, 1476),
            ],
        );

        $details = $this->present($booking);

        $this->assertNull($details['amount_hunting']);
        $this->assertNull($details['amount_hunting_per_person']);
    }

    /**
     * @param list<BookingService> $services
     */
    private function booking(
        ?float $amountHunting,
        int $totalHunting,
        int $durationDays,
        array $services,
    ): Booking {
        $booking = new Booking();
        $booking->id = 83;
        $booking->status = Booking::PAID;
        $booking->type = Booking::BookingTypeAnimal;
        $booking->create_user = 1;
        $booking->start_date = '2026-10-01';
        $booking->end_date = Carbon::parse('2026-10-01')->addDays($durationDays)->toDateString();
        $booking->total_hunting = $totalHunting;
        $booking->total_guests = $totalHunting;
        $booking->amount_hunting = $amountHunting;
        $booking->setRelation('hunterInvitations', collect());
        $booking->setRelation('bookingHunters', collect());
        $booking->setRelation('roomsBooking', collect());
        $animal = new Animal(['title' => 'Лось']);
        $animal->id = 7;
        $booking->setRelation('animal', $animal);
        $booking->setRelation('bookingServices', collect($services));

        return $booking;
    }

    private function service(string $type, float $price, ?int $hunterId = null): BookingService
    {
        $service = new BookingService();
        $service->service_type = $type;
        $service->price = $price;
        $service->hunter_id = $hunterId;

        return $service;
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Booking $booking, int $userId = 1): array
    {
        $actions = $this->createStub(BookingHistoryActionService::class);
        $actions->method('getAvailableActions')->willReturn([]);

        $user = new User();
        $user->id = $userId;

        return (new BookingHistoryItemPresenter($actions))
            ->present($booking, $user, 'customer')
            ->details;
    }
}
