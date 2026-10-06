<?php

namespace Tests\Feature\Booking;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Services\BookingStoreService;
use Tests\Support\PreparesAuthDatabase;
use Tests\TestCase;

class BookingCreateRequestTest extends TestCase
{
    use PreparesAuthDatabase;

    private int $hotelId;

    private int $roomId;

    private int $animalId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareAuthDatabase();

        $user = User::factory()->create([
            'email' => 'hunter@example.com',
            'password' => 'Secret123',
        ]);
        Sanctum::actingAs($user);

        $this->hotelId = DB::table('bc_hotels')->insertGetId([
            'slug' => 'hunting-base',
            'title' => 'Охотбаза',
            'status' => 'publish',
        ]);
        $this->roomId = DB::table('bc_hotel_rooms')->insertGetId([
            'parent_id' => $this->hotelId,
            'title' => 'Домик',
            'status' => 'publish',
        ]);
        $this->animalId = DB::table('bc_animals')->insertGetId([
            'title' => 'Кабан',
            'status' => 'publish',
        ]);
    }

    public function test_booking_requires_hotel_and_dates(): void
    {
        $response = $this->postJson('/api/v1/bookings', []);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('error_code', 'validation_error')
            ->assertJsonValidationErrors(['hotel_id', 'check_in', 'check_out']);
    }

    public function test_booking_rejects_check_in_in_the_past(): void
    {
        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'check_in' => now()->subDay()->toDateString(),
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['check_in'])
            ->assertJsonPath('errors.check_in.0', 'Дата заезда не может быть в прошлом');
    }

    public function test_booking_rejects_check_out_that_is_not_after_check_in(): void
    {
        $checkIn = now()->addDay()->toDateString();

        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'check_in' => $checkIn,
            'check_out' => $checkIn,
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['check_out']);
    }

    public function test_booking_rejects_unknown_hotel(): void
    {
        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'hotel_id' => 999,
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['hotel_id']);
    }

    public function test_booking_requires_room_or_animal(): void
    {
        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'rooms' => null,
            'animal_id' => null,
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rooms'])
            ->assertJsonPath('errors.rooms.0', 'Выберите номер или животное для охоты');
    }

    public function test_booking_rejects_room_from_another_hotel(): void
    {
        $otherHotelId = DB::table('bc_hotels')->insertGetId([
            'slug' => 'other-base',
            'title' => 'Другая база',
            'status' => 'publish',
        ]);
        $foreignRoomId = DB::table('bc_hotel_rooms')->insertGetId([
            'parent_id' => $otherHotelId,
            'title' => 'Чужой домик',
            'status' => 'publish',
        ]);

        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'rooms' => [
                ['room_id' => $foreignRoomId, 'number' => 1],
            ],
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rooms.0.room_id']);
    }

    public function test_booking_rejects_unpublished_room(): void
    {
        $draftRoomId = DB::table('bc_hotel_rooms')->insertGetId([
            'parent_id' => $this->hotelId,
            'title' => 'Черновик',
            'status' => 'draft',
        ]);

        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'rooms' => [
                ['room_id' => $draftRoomId, 'number' => 1],
            ],
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rooms.0.room_id']);
    }

    public function test_booking_rejects_unknown_animal(): void
    {
        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'rooms' => null,
            'animal_id' => 999,
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['animal_id']);
    }

    public function test_booking_rejects_room_number_below_one(): void
    {
        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'rooms' => [
                ['room_id' => $this->roomId, 'number' => 0],
            ],
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rooms.0.number']);
    }

    public function test_booking_accepts_published_room(): void
    {
        $booking = new Booking();
        $booking->code = 'BK-ROOM';

        $this->mock(BookingStoreService::class, function ($mock) use ($booking): void {
            $mock->shouldReceive('store')->once()->andReturn($booking);
        });

        $response = $this->postJson('/api/v1/bookings', $this->payload());

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.booking_code', 'BK-ROOM');
    }

    public function test_booking_accepts_animal_without_rooms(): void
    {
        $booking = new Booking();
        $booking->code = 'BK-ANIMAL';

        $this->mock(BookingStoreService::class, function ($mock) use ($booking): void {
            $mock->shouldReceive('store')->once()->andReturn($booking);
        });

        $response = $this->postJson('/api/v1/bookings', $this->payload([
            'rooms' => null,
            'animal_id' => $this->animalId,
        ]));

        $response
            ->assertCreated()
            ->assertJsonPath('data.booking_code', 'BK-ANIMAL');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'hotel_id' => $this->hotelId,
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'adults' => 2,
            'rooms' => [
                ['room_id' => $this->roomId, 'number' => 1],
            ],
        ], $overrides);
    }
}
