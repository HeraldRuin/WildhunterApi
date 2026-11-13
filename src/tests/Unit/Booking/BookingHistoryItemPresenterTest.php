<?php

namespace Tests\Unit\Booking;

use App\Models\User;
use Carbon\Carbon;
use Modules\Animals\Models\Animal;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Dto\BookingHistoryItemData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
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

        $this->assertSame(120000.0, $details['amount_hunting']);
        $this->assertSame(30000.0, $details['amount_hunting_per_person']);
        $this->assertSame(120000.0, $details['animal']['price_total']);
        $this->assertSame(30000.0, $details['animal']['price_per_person']);
        $this->assertSame(121476.0, $details['amount_hunting_with_services']);
        $this->assertSame(30369.0, $details['amount_hunting_with_services_per_person']);
        $this->assertSame(120000.0, (float) $booking->amount_hunting);
    }

    public function test_service_total_uses_saved_food_price_and_skips_spendings(): void
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

        $this->assertSame(10000.0, $details['amount_hunting']);
        $this->assertSame(5000.0, $details['amount_hunting_per_person']);
        $this->assertSame(10450.0, $details['amount_hunting_with_services']);
        $this->assertSame(5225.0, $details['amount_hunting_with_services_per_person']);
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

        $this->assertSame(120000.0, $charged['amount_hunting']);
        $this->assertSame(30000.0, $charged['amount_hunting_per_person']);
        $this->assertSame(30000.0, $other['amount_hunting_per_person']);
        $this->assertSame(121000.0, $charged['amount_hunting_with_services']);
        $this->assertSame(121000.0, $other['amount_hunting_with_services']);
        $this->assertSame(31000.0, $charged['amount_hunting_with_services_per_person']);
        $this->assertSame(30000.0, $other['amount_hunting_with_services_per_person']);
    }

    public function test_individual_additional_stays_on_the_selected_hunter(): void
    {
        $booking = $this->booking(
            amountHunting: 120000,
            totalHunting: 4,
            durationDays: 1,
            services: [
                $this->service(AddetionalPrice::ADDETIONAL, 12000, 5, AddetionalPrice::INDIVIDUAL),
                $this->service(AddetionalPrice::ADDETIONAL, 400, null, AddetionalPrice::PERSON),
            ],
        );

        $selected = $this->present($booking, 5);
        $other = $this->present($booking, 1);

        $this->assertSame(120000.0, $selected['amount_hunting']);
        $this->assertSame(30000.0, $selected['amount_hunting_per_person']);
        $this->assertSame(132400.0, $selected['amount_hunting_with_services']);
        $this->assertSame(132400.0, $other['amount_hunting_with_services']);
        $this->assertSame(42100.0, $selected['amount_hunting_with_services_per_person']);
        $this->assertSame(30100.0, $other['amount_hunting_with_services_per_person']);
    }

    public function test_accommodation_per_person_uses_planned_count_until_collection_ends(): void
    {
        $booking = $this->hotelBooking(Booking::START_COLLECTION, 9, 4);

        $details = $this->present($booking);

        $this->assertSame(9.0, $details['amount_accommodation']);
        $this->assertSame(2.25, $details['amount_accommodation_per_person']);
    }

    public function test_hunting_keeps_planned_count_until_collection_ends(): void
    {
        $booking = $this->huntingBooking(Booking::START_COLLECTION, 120000, 5, acceptedHunters: 4);

        $details = $this->present($booking);

        $this->assertSame(5, $details['total_hunting']);
        $this->assertSame(120000.0, $details['amount_hunting']);
        $this->assertSame(24000.0, $details['amount_hunting_per_person']);
        $this->assertSame(24000.0, $details['animal']['price_per_person']);
    }

    public function test_hunting_uses_accepted_hunters_after_collection(): void
    {
        $booking = $this->huntingBooking(Booking::PREPAYMENT_COLLECTION, 120000, 5, acceptedHunters: 4);

        $details = $this->present($booking);

        $this->assertSame(4, $details['total_hunting']);
        $this->assertSame(120000.0, $details['amount_hunting']);
        $this->assertSame(30000.0, $details['amount_hunting_per_person']);
        $this->assertSame(30000.0, $details['animal']['price_per_person']);
    }

    public function test_accommodation_per_person_uses_accepted_hunters_after_collection(): void
    {
        $booking = $this->hotelBooking(Booking::PREPAYMENT_COLLECTION, 9, 4, acceptedHunters: 3);

        $details = $this->present($booking);

        $this->assertSame(3.0, $details['amount_accommodation_per_person']);
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
        $this->assertNull($details['amount_hunting_with_services']);
        $this->assertNull($details['amount_hunting_with_services_per_person']);
    }

    public function test_paid_invitation_exposes_prepayment_share(): void
    {
        $booking = $this->hotelBooking(Booking::PAID, 9000, 3);
        $booking->duration_nights = 3;

        $paid = new BookingHunterInvitation();
        $paid->id = 1;
        $paid->hunter_id = 5;
        $paid->status = BookingHunterInvitation::STATUS_ACCEPTED;
        $paid->prepayment_paid = true;
        $paid->prepayment_paid_status = BookingHunterInvitation::PREPAYMENT_PAID;

        $pending = new BookingHunterInvitation();
        $pending->id = 2;
        $pending->hunter_id = 6;
        $pending->status = BookingHunterInvitation::STATUS_ACCEPTED;
        $pending->prepayment_paid = false;
        $pending->prepayment_paid_status = BookingHunterInvitation::PREPAYMENT_PENDING;

        $booking->setRelation('hunterInvitations', collect([$paid, $pending]));

        $invitations = $this->presentItem($booking)->collection['invitations'];

        $this->assertSame(1500.0, $invitations[0]['prepayment_amount']);
        $this->assertNull($invitations[1]['prepayment_amount']);
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

    private function service(
        string $type,
        float $price,
        ?int $hunterId = null,
        ?string $calculationType = null,
    ): BookingService {
        $service = new BookingService();
        $service->service_type = $type;
        $service->price = $price;
        $service->hunter_id = $hunterId;
        $service->calculation_type = $calculationType;

        return $service;
    }

    /**
     * @return array<string, mixed>
     */
    private function huntingBooking(
        string $status,
        float $amountHunting,
        int $plannedHunters,
        int $acceptedHunters,
    ): Booking {
        $booking = new class extends Booking {
            public int $acceptedHunters = 0;

            public function countAcceptedHunters(): int
            {
                return $this->acceptedHunters;
            }
        };

        $booking->acceptedHunters = $acceptedHunters;
        $booking->id = 106;
        $booking->status = $status;
        $booking->type = Booking::BookingTypeHotelAnimal;
        $booking->create_user = 1;
        $booking->start_date = '2026-11-19';
        $booking->end_date = '2026-11-22';
        $booking->total_hunting = $plannedHunters;
        $booking->total_guests = $plannedHunters;
        $booking->amount_hunting = $amountHunting;
        $booking->setRelation('hunterInvitations', collect());
        $booking->setRelation('bookingHunters', collect());
        $booking->setRelation('roomsBooking', collect());
        $animal = new Animal(['title' => 'Лось']);
        $animal->id = 7;
        $booking->setRelation('animal', $animal);
        $booking->setRelation('bookingServices', collect());

        return $booking;
    }

    private function hotelBooking(
        string $status,
        float $total,
        int $hunters,
        ?int $acceptedHunters = null,
    ): Booking {
        $booking = $acceptedHunters === null
            ? new Booking()
            : new class extends Booking {
                public int $acceptedHunters = 0;

                public function countAcceptedHunters(): int
                {
                    return $this->acceptedHunters;
                }
            };

        if ($acceptedHunters !== null) {
            $booking->acceptedHunters = $acceptedHunters;
        }

        $booking->id = 83;
        $booking->status = $status;
        $booking->type = Booking::BookingTypeHotelAnimal;
        $booking->create_user = 1;
        $booking->start_date = '2026-10-01';
        $booking->end_date = '2026-10-04';
        $booking->total = $total;
        $booking->total_hunting = $hunters;
        $booking->total_guests = $hunters;
        $booking->amount_hunting = null;
        $booking->setRelation('hunterInvitations', collect());
        $booking->setRelation('bookingHunters', collect());
        $booking->setRelation('roomsBooking', collect());
        $booking->setRelation('animal', null);
        $booking->setRelation('bookingServices', collect());

        return $booking;
    }

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

    private function presentItem(Booking $booking, int $userId = 1): BookingHistoryItemData
    {
        $actions = $this->createStub(BookingHistoryActionService::class);
        $actions->method('getAvailableActions')->willReturn([]);

        $user = new User();
        $user->id = $userId;

        return (new BookingHistoryItemPresenter($actions))
            ->present($booking, $user, 'customer');
    }
}
