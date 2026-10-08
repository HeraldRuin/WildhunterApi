<?php

namespace Tests\Unit\Booking;

use App\Exceptions\ValidationException;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Animals\Models\Animal;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;
use Modules\Booking\Services\Calculation\BookingCalculator;
use Modules\Booking\Services\Calculation\Strategies\BookingCalculationStrategyResolver;
use Modules\Booking\Services\Calculation\Strategies\HotelCalculationStrategy;
use Modules\Booking\Services\Calculation\Strategies\HotelHuntingCalculationStrategy;
use Modules\Booking\Services\Calculation\Strategies\HuntingCalculationStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingCalculationStrategyTest extends TestCase
{
    private BookingCalculationStrategyResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new BookingCalculationStrategyResolver();
        $this->prepareRoomTables();
    }

    #[DataProvider('bookingTypes')]
    public function test_resolver_picks_strategy_by_booking_type(string $type, string $strategy): void
    {
        $booking = new Booking();
        $booking->type = $type;

        $this->assertInstanceOf($strategy, $this->resolver->resolve($booking));
    }

    public function test_unknown_booking_type_is_rejected(): void
    {
        $booking = new Booking();
        $booking->type = 'cruise';

        try {
            $this->resolver->resolve($booking);
            $this->fail('Неизвестный тип брони должен быть отклонён');
        } catch (ValidationException $exception) {
            $this->assertSame('unknown_booking_type', $exception->getErrorCode());
            $this->assertSame('booking', $exception->getDomain());
            $this->assertSame(['type' => 'cruise'], $exception->getContext());
        }
    }

    public function test_hotel_strategy_stops_without_paid_participants(): void
    {
        $result = (new HotelCalculationStrategy(new BookingCalculator()))->calculate(
            new Booking(),
            $this->data(paidCount: 0),
            $this->user(),
        );

        $this->assertSame([
            'success' => false,
            'message' => 'no_paid_participants',
        ], $result);
    }

    public function test_hotel_hunting_strategy_stops_without_paid_participants(): void
    {
        $result = (new HotelHuntingCalculationStrategy(new BookingCalculator()))->calculate(
            new Booking(),
            $this->data(paidCount: 0),
            $this->user(),
        );

        $this->assertSame([
            'success' => false,
            'message' => 'no_paid_participants',
        ], $result);
    }

    public function test_hunting_strategy_stops_without_accepted_hunters(): void
    {
        $strategy = new HuntingCalculationStrategy(new BookingCalculator());

        foreach ([0, null] as $totalHunting) {
            $result = $strategy->calculate(
                new Booking(),
                $this->data(totalHunting: $totalHunting),
                $this->user(),
            );

            $this->assertSame([
                'success' => false,
                'message' => 'no_hunters',
            ], $result);
        }
    }

    public function test_hotel_strategy_assembles_stay_prepayment_and_balance(): void
    {
        $booking = $this->booking([
            'type' => Booking::BookingTypeHotel,
            'total' => 1000,
        ]);
        $food = $this->service(AddetionalPrice::FOOD, 100, ['type' => 'Завтрак']);

        $result = (new HotelCalculationStrategy(new BookingCalculator()))->calculate(
            $booking,
            $this->data(services: collect([$food]), paidCount: 2),
            $this->user(),
        );

        $this->assertTrue($result['success']);
        $this->assertFalse($result['trophy_show']);
        $this->assertFalse($result['penalties_show']);
        $this->assertTrue($result['additional_services_show']);
        $this->assertSame('Проживание, 3 суток', $result['items'][0]['name']);
        $this->assertMoney(1000, $result['items'][0]['total_cost']);
        $this->assertMoney(0, $result['items'][0]['my_cost']);
        $this->assertMoney(300, $result['meals'][0]['total_cost']);
        $this->assertMoney(150, $result['meals'][0]['my_cost']);
        $this->assertSame(
            ['Внесена предоплата', 'Остаток базе', 'Итог охотникам'],
            array_column($result['all_items'], 'name'),
        );
        $this->assertMoney(1000, $result['all_items'][0]['total_cost']);
        $this->assertMoney(500, $result['all_items'][0]['my_cost']);
        $this->assertMoney(300, $result['all_items'][1]['total_cost']);
        $this->assertMoney(-350, $result['all_items'][1]['my_cost']);
        $this->assertMoney(1000, $result['prepaid_total']);
        $this->assertMoney(300, $result['base_total']);
        $this->assertMoney(300, $result['total']);
    }

    public function test_hunting_strategy_assembles_organisation_and_own_charges(): void
    {
        $booking = $this->booking([
            'type' => Booking::BookingTypeAnimal,
            'amount_hunting' => 8000,
            'total_hunting' => 4,
        ]);
        $user = $this->user();
        $trophy = $this->service(AddetionalPrice::TROPHY, 100, ['type' => 'рога', 'count' => 1]);
        $trophy->setRelation('animal', $this->animal());
        $penalty = $this->service(AddetionalPrice::PENALTY, 70, [
            'hunter_id' => 9,
            'animal_id' => 1,
            'type' => 'подранок',
        ]);
        $penalty->setRelation('animal', $this->animal());
        $foreignService = $this->service(AddetionalPrice::ADDETIONAL, 90, [
            'calculation_type' => AddetionalPrice::INDIVIDUAL,
            'hunter_id' => 9,
            'type' => 'Снегоход',
        ]);

        $result = (new HuntingCalculationStrategy(new BookingCalculator()))->calculate(
            $booking,
            $this->data(
                services: collect([$trophy, $penalty, $foreignService]),
                totalHunting: 2,
            ),
            $user,
        );

        $this->assertTrue($result['success']);
        $this->assertTrue($result['trophy_show']);
        $this->assertTrue($result['penalties_show']);
        $this->assertSame('Организация охоты', $result['items'][0]['name']);
        $this->assertMoney(4000, $result['items'][0]['total_cost']);
        $this->assertMoney(2000, $result['items'][0]['my_cost']);
        $this->assertMoney(50, $result['trophies'][0]['my_cost']);
        $this->assertMoney(0, $result['penalties'][0]['my_cost']);
        $this->assertSame(0, $result['addetionals'][0]['my_cost']);
        $this->assertSame(['Остаток базе', 'Итог охотникам'], array_column($result['all_items'], 'name'));
        $this->assertMoney(4260, $result['all_items'][0]['total_cost']);
        $this->assertMoney(2050, $result['all_items'][0]['my_cost']);
        $this->assertMoney(4260, $result['base_total']);
    }

    public function test_hotel_hunting_strategy_includes_stay_and_hunt(): void
    {
        $booking = $this->booking([
            'type' => Booking::BookingTypeHotelAnimal,
            'total' => 1000,
            'amount_hunting' => 8000,
            'total_hunting' => 4,
        ]);

        $result = (new HotelHuntingCalculationStrategy(new BookingCalculator()))->calculate(
            $booking,
            $this->data(paidCount: 2),
            $this->user(),
        );

        $this->assertTrue($result['success']);
        $this->assertSame(
            ['Проживание, 3 суток', 'Организация охоты'],
            array_column($result['items'], 'name'),
        );
        $this->assertTrue($result['items'][1]['has_tooltip']);
        $this->assertSame(
            ['Внесена предоплата', 'Остаток базе', 'Итог охотникам'],
            array_column($result['all_items'], 'name'),
        );
        $this->assertMoney(1000, $result['prepaid_total']);
        $this->assertMoney(4000, $result['items'][1]['total_cost']);
        $this->assertMoney(4000, $result['base_total']);
        $this->assertMoney(4000, $result['total']);
    }

    /**
     * @return array<string, array{0: string, 1: class-string}>
     */
    public static function bookingTypes(): array
    {
        return [
            'hotel' => [Booking::BookingTypeHotel, HotelCalculationStrategy::class],
            'animal' => [Booking::BookingTypeAnimal, HuntingCalculationStrategy::class],
            'hotel_animal' => [Booking::BookingTypeHotelAnimal, HotelHuntingCalculationStrategy::class],
        ];
    }

    private function prepareRoomTables(): void
    {
        Schema::dropIfExists('bc_booking_room_places');
        Schema::dropIfExists('bc_hotel_room_bookings');

        Schema::create('bc_booking_room_places', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
        });

        Schema::create('bc_hotel_room_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->decimal('price', 12, 2)->nullable();
        });
    }

    private function booking(array $attributes = []): Booking
    {
        $booking = new Booking();
        $booking->id = 15;
        $booking->forceFill(array_merge([
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-08',
            'is_paid' => false,
            'total' => 0,
        ], $attributes));

        return $booking;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function data(
        mixed $services = null,
        int $paidCount = 1,
        mixed $totalHunting = 1,
        bool $isBaseAdmin = false,
    ): array {
        return [
            'services' => $services ?? collect(),
            'paidCount' => $paidCount,
            'totalHunting' => $totalHunting,
            'isBaseAdmin' => $isBaseAdmin,
        ];
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

    private function user(): User
    {
        $user = new User();
        $user->id = 4;

        return $user;
    }

    private function animal(): Animal
    {
        $animal = new Animal();
        $animal->title = 'Лось';

        return $animal;
    }

    private function assertMoney(float|int $expected, mixed $actual): void
    {
        $this->assertEqualsWithDelta((float) $expected, (float) $actual, 0.001);
    }
}
