<?php

namespace Tests\Unit\Booking;

use App\Models\User;
use Modules\Animals\Models\Animal;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;
use Modules\Booking\Services\Calculation\BookingCalculator;
use Tests\TestCase;

class BookingCalculatorTest extends TestCase
{
    private BookingCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new BookingCalculator();
    }

    public function test_food_is_multiplied_by_duration_days_and_the_sum_is_rounded(): void
    {
        $booking = $this->booking();
        $services = collect([
            $this->service(AddetionalPrice::FOOD, 100),
            $this->service(AddetionalPrice::FOOD, 50),
        ]);

        $totals = $this->calculator->getServiceCount($booking, $services, [AddetionalPrice::FOOD]);

        $this->assertSame(3.0, $booking->duration_days);
        $this->assertMoney(450, $totals['food']);

        $rounded = $this->calculator->getServiceCount(
            $this->booking(end: '2026-10-06'),
            collect([
                $this->service(AddetionalPrice::FOOD, 10.4),
                $this->service(AddetionalPrice::FOOD, 10.4),
            ]),
            [AddetionalPrice::FOOD],
        );

        $this->assertMoney(21, $rounded['food']);
    }

    public function test_meal_lines_multiply_by_duration_and_zero_hunters_get_nothing(): void
    {
        $meals = $this->calculator->calculateMeals(
            collect([$this->service(AddetionalPrice::FOOD, 400, ['type' => 'Завтрак'])]),
            0,
            $this->booking(),
        );

        $this->assertSame('Завтрак', $meals[0]['name']);
        $this->assertMoney(1200, $meals[0]['total_cost']);
        $this->assertSame(0, $meals[0]['my_cost']);
    }

    public function test_meal_share_is_rounded_per_line(): void
    {
        $meals = $this->calculator->calculateMeals(
            collect([$this->service(AddetionalPrice::FOOD, 100)]),
            3,
            $this->booking(end: '2026-10-06'),
        );

        $this->assertMoney(100, $meals[0]['total_cost']);
        $this->assertMoney(33, $meals[0]['my_cost']);
    }

    public function test_trophies_and_preparations_are_not_split_when_there_are_no_hunters(): void
    {
        $trophy = $this->service(AddetionalPrice::TROPHY, 900, ['type' => 'рога', 'count' => 2]);
        $trophy->setRelation('animal', $this->animal('Лось'));
        $preparation = $this->service(AddetionalPrice::PREPARATION, 300, ['count' => 1]);
        $preparation->setRelation('animal', $this->animal('Лось'));

        $trophies = $this->calculator->calculateTrophies(collect([$trophy]), 0);
        $preparations = $this->calculator->calculatePreparations(collect([$preparation]), 0);

        $this->assertSame('Лось (рога x 2шт)', $trophies[0]['name']);
        $this->assertMoney(900, $trophies[0]['total_cost']);
        $this->assertSame(0, $trophies[0]['my_cost']);
        $this->assertSame('Разделка (Лось x 1шт)', $preparations[0]['name']);
        $this->assertMoney(300, $preparations[0]['total_cost']);
        $this->assertSame(0, $preparations[0]['my_cost']);
    }

    public function test_service_share_rounds_the_sum_across_hunters(): void
    {
        $booking = $this->booking(end: '2026-10-06');
        $services = collect([
            $this->service(AddetionalPrice::TROPHY, 100),
            $this->service(AddetionalPrice::TROPHY, 100),
        ]);

        $totals = $this->calculator->getServiceMyCount(
            $booking,
            $this->user(1),
            3,
            $services,
            [AddetionalPrice::TROPHY],
        );

        $this->assertMoney(67, $totals['trophy']);
    }

    public function test_food_share_divides_the_stay_across_hunters(): void
    {
        $totals = $this->calculator->getServiceMyCount(
            $this->booking(),
            $this->user(1),
            2,
            collect([$this->service(AddetionalPrice::FOOD, 100)]),
            [AddetionalPrice::FOOD],
        );

        $this->assertMoney(150, $totals['food']);
    }

    public function test_individual_service_for_another_hunter_is_zero(): void
    {
        $foreign = $this->service(AddetionalPrice::ADDETIONAL, 800, [
            'calculation_type' => AddetionalPrice::INDIVIDUAL,
            'hunter_id' => 9,
            'type' => 'Снегоход',
        ]);
        $own = $this->service(AddetionalPrice::ADDETIONAL, 500, [
            'calculation_type' => AddetionalPrice::INDIVIDUAL,
            'hunter_id' => 4,
            'type' => 'Баня',
        ]);
        $user = $this->user(4);

        $lines = $this->calculator->calculateAdditional(collect([$foreign, $own]), $user, 3);

        $this->assertSame(0, $lines[0]['my_cost']);
        $this->assertMoney(800, $lines[0]['total_cost']);
        $this->assertMoney(500, $lines[1]['my_cost']);

        $totals = $this->calculator->getServiceMyCount(
            $this->booking(),
            $user,
            3,
            collect([$foreign, $own]),
            [AddetionalPrice::ADDETIONAL],
        );

        $this->assertMoney(500, $totals['additional']);
    }

    public function test_per_person_service_is_split_and_rounded(): void
    {
        $item = $this->service(AddetionalPrice::ADDETIONAL, 100, [
            'calculation_type' => AddetionalPrice::PERSON,
            'type' => 'Трансфер',
        ]);

        $lines = $this->calculator->calculateAdditional(collect([$item]), $this->user(4), 3);

        $this->assertMoney(33, $lines[0]['my_cost']);
        $this->assertMoney(100, $lines[0]['total_cost']);
    }

    public function test_penalty_is_charged_only_to_its_hunter(): void
    {
        $animal = $this->animal('Кабан');
        $mine = $this->service(AddetionalPrice::PENALTY, 40, [
            'hunter_id' => 4,
            'animal_id' => 1,
            'type' => 'подранок',
        ]);
        $mine->setRelation('animal', $animal);
        $theirs = $this->service(AddetionalPrice::PENALTY, 70, [
            'hunter_id' => 9,
            'animal_id' => 1,
            'type' => 'подранок',
        ]);
        $theirs->setRelation('animal', $animal);
        $user = $this->user(4);

        $lines = $this->calculator->calculatePenalties(collect([$mine, $theirs]), $user);
        $totals = $this->calculator->getServiceMyCount(
            $this->booking(),
            $user,
            3,
            collect([$mine, $theirs]),
            [AddetionalPrice::PENALTY],
        );

        $this->assertSame('подранок (Кабан)', $lines[0]['name']);
        $this->assertMoney(110, $lines[0]['total_cost']);
        $this->assertMoney(40, $lines[0]['my_cost']);
        $this->assertMoney(40, $totals['penalty']);
    }

    public function test_hunt_organisation_is_zero_without_hunters_or_a_hunt_total(): void
    {
        $booking = $this->booking([
            'amount_hunting' => 8000,
            'total_hunting' => 4,
        ]);

        $this->assertMoney(0, $this->calculator->calculateOrganisationHunting($booking, 0));
        $this->assertMoney(0, $this->calculator->calculateOrganisationHunting(
            $this->booking(['amount_hunting' => 8000, 'total_hunting' => 0]),
            3,
        ));
        $this->assertMoney(0, $this->calculator->calculateOrganisationHunting(
            $this->booking(['amount_hunting' => 8000, 'total_hunting' => null]),
            3,
        ));
    }

    public function test_hunt_organisation_rounds_the_share_of_accepted_hunters(): void
    {
        $booking = $this->booking([
            'amount_hunting' => 8000,
            'total_hunting' => 4,
        ]);

        $this->assertMoney(6000, $this->calculator->calculateOrganisationHunting($booking, 3));
        $this->assertMoney(2000, $this->calculator->calculateMyOrganisationHunting($booking, 3));
    }

    public function test_prepayment_share_rounds_half_up(): void
    {
        $this->assertMoney(1, $this->calculator->myPrepaymentMade(1, 2));
        $this->assertMoney(333, $this->calculator->myPrepaymentMade(1000, 3));
        $this->assertMoney(1000, $this->calculator->basePrepaymentMade($this->booking(['total' => 1000])));
    }

    public function test_history_totals_keep_prepayment_for_lodging_and_drop_paid_balance(): void
    {
        $booking = $this->booking([
            'total' => 900,
            'amount_hunting' => 8000,
            'total_hunting' => 4,
            'type' => Booking::BookingTypeHotel,
        ]);
        $services = collect();

        $open = $this->calculator->getBookingTotal($booking, $services, 3);

        $this->assertMoney(900, $open['prepaid_total']);
        $this->assertMoney(6000, $open['base_total']);
        $this->assertMoney(6000, $open['total']);

        $booking->is_paid = true;
        $paid = $this->calculator->getBookingTotal($booking, $services, 3);

        $this->assertMoney(900, $paid['prepaid_total']);
        $this->assertSame(0, $paid['base_total']);
        $this->assertMoney(6000, $paid['total']);
    }

    public function test_history_totals_for_hunting_have_no_prepayment(): void
    {
        $booking = $this->booking([
            'type' => Booking::BookingTypeAnimal,
            'amount_hunting' => 8000,
            'total_hunting' => 4,
        ]);

        $open = $this->calculator->getBookingTotal($booking, collect(), 3);

        $this->assertSame(0, $open['prepaid_total']);
        $this->assertMoney(6000, $open['base_total']);
        $this->assertSame(0, $open['total']);

        $booking->is_paid = true;

        $this->assertSame(0, $this->calculator->getBookingTotal($booking, collect(), 3)['base_total']);
    }

    public function test_unknown_booking_type_has_empty_history_totals(): void
    {
        $booking = $this->booking(['type' => 'cruise']);

        $this->assertSame(
            ['prepaid_total' => 0, 'base_total' => 0, 'total' => 0],
            $this->calculator->getBookingTotal($booking, collect(), 2),
        );
    }

    public function test_paid_hunting_balance_for_base_admin_is_zero(): void
    {
        $booking = $this->booking([
            'type' => Booking::BookingTypeAnimal,
            'amount_hunting' => 8000,
            'total_hunting' => 4,
            'is_paid' => true,
        ]);

        $balance = $this->calculator->getBalanceBaseHunting($booking, $this->user(4), collect(), 3, true);

        $this->assertSame('Остаток базе', $balance['title_name']);
        $this->assertSame(0, $balance['total_cost']);
        $this->assertMoney(2000, $balance['my_cost']);
    }

    public function test_another_hunters_spending_is_split_and_own_spending_is_not_a_debt(): void
    {
        $foreign = $this->service(AddetionalPrice::SPENDING, 100, [
            'hunter_id' => 9,
            'comment' => 'патроны',
        ]);
        $foreign->setRelation('hunter', $this->user(9, 'Иванов'));
        $own = $this->service(AddetionalPrice::SPENDING, 50, [
            'hunter_id' => 4,
            'comment' => 'бензин',
        ]);
        $own->setRelation('hunter', $this->user(4, 'Петров'));

        $spendings = $this->calculator->calculateSpendings(collect([$foreign, $own]), $this->user(4), 3);

        $this->assertSame('Иванов (патроны)', $spendings['items'][0]['name']);
        $this->assertMoney(33, $spendings['items'][0]['my_cost']);
        $this->assertSame(0, $spendings['items'][1]['my_cost']);
        $this->assertMoney(150, $spendings['total_spending']);
        $this->assertMoney(33, $spendings['total_my_debt']);
    }

    private function booking(array $attributes = [], ?string $end = null): Booking
    {
        $booking = new Booking();
        $booking->forceFill(array_merge([
            'start_date' => '2026-10-05',
            'end_date' => $end ?? '2026-10-08',
            'is_paid' => false,
            'type' => Booking::BookingTypeHotel,
            'total' => 0,
        ], $attributes));

        return $booking;
    }

    private function service(string $serviceType, float|int $price, array $extra = []): BookingService
    {
        $service = new BookingService();
        $service->forceFill(array_merge([
            'service_type' => $serviceType,
            'price' => $price,
        ], $extra));

        return $service;
    }

    private function user(int $id, string $lastName = ''): User
    {
        $user = new User();
        $user->id = $id;
        $user->last_name = $lastName;

        return $user;
    }

    private function animal(string $title): Animal
    {
        $animal = new Animal();
        $animal->title = $title;

        return $animal;
    }

    private function assertMoney(float|int $expected, mixed $actual): void
    {
        $this->assertEqualsWithDelta((float) $expected, (float) $actual, 0.001);
    }
}
