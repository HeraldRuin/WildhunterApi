<?php

namespace Tests\Feature\Hotel;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Hotel\Dto\StoreRoomAvailabilityData;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelRoomDate;
use Modules\Hotel\Services\RoomAvailabilityService;
use Modules\Role\Models\Role;
use Tests\Support\PreparesHotelSearchDatabase;
use Tests\TestCase;

class RoomAvailabilityServiceTest extends TestCase
{
    use PreparesHotelSearchDatabase;

    private RoomAvailabilityService $availability;

    private HotelRoom $room;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareHotelSearchDatabase();

        $roleId = DB::table('core_roles')->insertGetId([
            'code' => Role::ADMIN,
            'name' => 'Админ базы',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role_id' => $roleId,
        ]);
        $this->actingAs($this->admin);

        $hotelId = DB::table('bc_hotels')->insertGetId([
            'slug' => 'base',
            'title' => 'Охотбаза',
            'status' => 'publish',
            'price' => 1000,
            'admin_base' => $this->admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $roomId = DB::table('bc_hotel_rooms')->insertGetId([
            'parent_id' => $hotelId,
            'title' => 'Домик',
            'status' => 'publish',
            'price' => 4500,
            'number' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->room = HotelRoom::query()->findOrFail($roomId);
        $this->availability = $this->app->make(RoomAvailabilityService::class);
    }

    public function test_store_dates_writes_every_day_in_the_range(): void
    {
        $result = $this->availability->storeDates(
            $this->room,
            $this->availabilityData('2026-10-05', '2026-10-07', 3200),
            $this->admin,
        );

        $this->assertSame(3, $result['data']['updated_days']);
        $this->assertSame(
            ['2026-10-05', '2026-10-06', '2026-10-07'],
            $this->storedDates(),
        );
        $this->assertEquals(3200.0, (float) HotelRoomDate::query()->first()->price);
    }

    public function test_store_dates_keeps_only_selected_weekdays(): void
    {
        $result = $this->availability->storeDates(
            $this->room,
            $this->availabilityData('2026-10-05', '2026-10-12', null, [1, 3]),
            $this->admin,
        );

        $this->assertSame(3, $result['data']['updated_days']);
        $this->assertSame(
            ['2026-10-05', '2026-10-07', '2026-10-12'],
            $this->storedDates(),
        );
    }

    public function test_store_dates_uses_room_price_when_price_is_missing(): void
    {
        $this->availability->storeDates(
            $this->room,
            $this->availabilityData('2026-10-05', '2026-10-05', null),
            $this->admin,
        );

        $row = HotelRoomDate::query()->first();
        $this->assertNotNull($row);
        $this->assertEquals(4500.0, (float) $row->price);
        $this->assertSame(2, (int) $row->number);
        $this->assertSame(1, (int) $row->active);
    }

    /**
     * @param  list<int>  $weekdays
     */
    private function availabilityData(string $start, string $end, ?float $price, array $weekdays = []): StoreRoomAvailabilityData
    {
        return new StoreRoomAvailabilityData(
            startDate: $start,
            endDate: $end,
            active: true,
            price: $price,
            number: 2,
            dayOfWeekSelect: $weekdays,
            isInstant: false,
        );
    }

    /**
     * @return list<string>
     */
    private function storedDates(): array
    {
        return HotelRoomDate::query()
            ->orderBy('start_date')
            ->pluck('start_date')
            ->map(static fn ($date): string => substr((string) $date, 0, 10))
            ->all();
    }
}
