<?php

namespace Tests\Feature\Hotel;

use Illuminate\Support\Facades\DB;
use Modules\Hotel\Dto\HotelSearchData;
use Modules\Hotel\Services\HotelSearchService;
use Tests\Support\PreparesHotelSearchDatabase;
use Tests\TestCase;

class HotelSearchServiceTest extends TestCase
{
    use PreparesHotelSearchDatabase;

    private HotelSearchService $search;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareHotelSearchDatabase();
        $this->search = $this->app->make(HotelSearchService::class);
    }

    public function test_price_filter_keeps_hotels_inside_the_range(): void
    {
        $cheap = $this->hotel('cheap', 1000);
        $match = $this->hotel('match', 3000);
        $expensive = $this->hotel('expensive', 8000);
        $this->hotel('draft', 3000, ['status' => 'draft']);

        $ids = $this->searchIds(['price' => ['min' => 2000, 'max' => 5000]]);

        $this->assertSame([$match], $ids);
        $this->assertNotContains($cheap, $ids);
        $this->assertNotContains($expensive, $ids);
    }

    public function test_animal_filter_keeps_hotels_with_available_animal(): void
    {
        $boar = $this->animal('Кабан');
        $wolf = $this->animal('Волк');
        $withBoar = $this->hotel('with-boar');
        $unavailable = $this->hotel('unavailable-boar');
        $withWolf = $this->hotel('with-wolf');

        $this->attachAnimal($withBoar, $boar, 'available');
        $this->attachAnimal($unavailable, $boar, 'unavailable');
        $this->attachAnimal($withWolf, $wolf, 'available');

        $ids = $this->searchIds(['animal_id' => $boar]);

        $this->assertSame([$withBoar], $ids);
    }

    public function test_hunting_method_filter_keeps_hotels_with_selected_method(): void
    {
        $driven = $this->huntingMethod('Загонная');
        $ambush = $this->huntingMethod('С вышки');
        $drivenHotel = $this->hotel('driven');
        $ambushHotel = $this->hotel('ambush');

        $this->attachHuntingMethod($drivenHotel, $driven);
        $this->attachHuntingMethod($ambushHotel, $ambush);

        $ids = $this->searchIds(['huntingMethodIds' => [$driven]]);

        $this->assertSame([$drivenHotel], $ids);
    }

    public function test_star_rate_filter_maps_rating_labels_to_scores(): void
    {
        $excellent = $this->hotel('excellent', 1000, ['star_rate' => 5]);
        $poor = $this->hotel('poor', 1000, ['star_rate' => 2]);

        $ids = $this->searchIds(['star_rate' => ['excellent']]);

        $this->assertSame([$excellent], $ids);
        $this->assertNotContains($poor, $ids);
    }

    public function test_location_filter_includes_nested_locations(): void
    {
        $region = $this->location('Область', 1, 6);
        $district = $this->location('Район', 2, 3, $region);
        $outside = $this->location('Другая область', 7, 8);
        $insideHotel = $this->hotel('inside', 1000, ['location_id' => $district]);
        $outsideHotel = $this->hotel('outside', 1000, ['location_id' => $outside]);

        $ids = $this->searchIds(['location_id' => $region]);

        $this->assertSame([$insideHotel], $ids);
        $this->assertNotContains($outsideHotel, $ids);
    }

    public function test_date_filter_excludes_blocked_nights_and_keeps_checkout_day(): void
    {
        $blockedOnLastNight = $this->hotel('blocked-night');
        $blockedOnCheckout = $this->hotel('blocked-checkout');
        $open = $this->hotel('open');

        $this->blockRoom($blockedOnLastNight, '2026-10-07');
        $this->blockRoom($blockedOnCheckout, '2026-10-08');

        $ids = $this->searchIds([
            'startDate' => '2026-10-05',
            'endDate' => '2026-10-08',
        ]);

        $this->assertSame([$blockedOnCheckout, $open], $ids);
        $this->assertNotContains($blockedOnLastNight, $ids);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return list<int>
     */
    private function searchIds(array $overrides = []): array
    {
        $ids = $this->search->search($this->searchData($overrides))->pluck('id')->all();
        sort($ids);

        return $ids;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function searchData(array $overrides = []): HotelSearchData
    {
        return new HotelSearchData(
            location_id: $overrides['location_id'] ?? null,
            locationIds: $overrides['locationIds'] ?? null,
            animal_id: $overrides['animal_id'] ?? null,
            animalIds: $overrides['animalIds'] ?? null,
            huntingMethodIds: $overrides['huntingMethodIds'] ?? null,
            startDate: $overrides['startDate'] ?? '2026-10-05',
            endDate: $overrides['endDate'] ?? '2026-10-08',
            adults: 1,
            children: 0,
            star_rate: $overrides['star_rate'] ?? null,
            price: $overrides['price'] ?? null,
            termIds: null,
            sort: null,
            order_by: null,
            order_direction: null,
            limit: null,
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function hotel(string $slug, float $price = 1000, array $overrides = []): int
    {
        return DB::table('bc_hotels')->insertGetId(array_merge([
            'slug' => $slug,
            'title' => $slug,
            'status' => 'publish',
            'price' => $price,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function animal(string $title): int
    {
        return DB::table('bc_animals')->insertGetId([
            'title' => $title,
            'status' => 'publish',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachAnimal(int $hotelId, int $animalId, string $status): void
    {
        DB::table('bc_hotel_animals')->insert([
            'hotel_id' => $hotelId,
            'animal_id' => $animalId,
            'status' => $status,
        ]);
    }

    private function huntingMethod(string $name): int
    {
        return DB::table('bc_hunting_methods')->insertGetId([
            'name' => $name,
            'slug' => str_replace(' ', '-', mb_strtolower($name)),
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachHuntingMethod(int $hotelId, int $methodId): void
    {
        DB::table('bc_hotel_hunting_methods')->insert([
            'hotel_id' => $hotelId,
            'hunting_method_id' => $methodId,
        ]);
    }

    private function location(string $name, int $left, int $right, ?int $parentId = null): int
    {
        return DB::table('bc_locations')->insertGetId([
            'name' => $name,
            'status' => 'publish',
            'parent_id' => $parentId,
            '_lft' => $left,
            '_rgt' => $right,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function blockRoom(int $hotelId, string $date): void
    {
        $roomId = DB::table('bc_hotel_rooms')->insertGetId([
            'parent_id' => $hotelId,
            'title' => 'Домик',
            'status' => 'publish',
            'price' => 1000,
            'number' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('bc_hotel_room_dates')->insert([
            'target_id' => $roomId,
            'start_date' => $date.' 00:00:00',
            'end_date' => $date.' 00:00:00',
            'price' => 1000,
            'number' => 0,
            'active' => 0,
            'is_instant' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
