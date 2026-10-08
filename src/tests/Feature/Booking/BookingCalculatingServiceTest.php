<?php

namespace Tests\Feature\Booking;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Services\Calculation\BookingCalculatingService;
use Modules\Role\Models\Role;
use Tests\TestCase;

class BookingCalculatingServiceTest extends TestCase
{
    private BookingCalculatingService $calculating;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabase();
        $this->calculating = $this->app->make(BookingCalculatingService::class);
    }

    public function test_admin_of_another_base_cannot_see_calculation(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);
        $owner = User::factory()->create();
        $foreignHotelId = $this->hotel($owner->id);
        $this->hotel($admin->id);
        $this->booking($foreignHotelId, Booking::PAID);

        $this->actingAs($admin);

        $this->assertDenied(
            fn () => $this->calculating->getByCode('BK-CALC', $admin),
            ForbiddenException::class,
            'booking_access_denied',
            'booking',
        );
    }

    public function test_hunter_without_accepted_invitation_cannot_see_calculation(): void
    {
        $hunter = $this->userWithRole(Role::CUSTOMER);
        $bookingId = $this->booking($this->hotel($hunter->id), Booking::PAID);
        $this->invitation($bookingId, $hunter->id, BookingHunterInvitation::STATUS_PENDING);

        $this->actingAs($hunter);

        $this->assertDenied(
            fn () => $this->calculating->getByCode('BK-CALC', $hunter),
            ForbiddenException::class,
            'booking_access_denied',
            'booking',
        );
    }

    public function test_hunter_without_invitation_cannot_see_calculation(): void
    {
        $hunter = $this->userWithRole(Role::CUSTOMER);
        $this->booking($this->hotel(null), Booking::PAID);

        $this->actingAs($hunter);

        $this->assertDenied(
            fn () => $this->calculating->getByCode('BK-CALC', $hunter),
            ForbiddenException::class,
            'booking_access_denied',
            'booking',
        );
    }

    public function test_calculation_is_unavailable_outside_the_allowed_status(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);
        $this->booking($this->hotel($admin->id), Booking::PROCESSING, Booking::BookingTypeHotel);

        $this->actingAs($admin);

        $this->assertDenied(
            fn () => $this->calculating->getByCode('BK-CALC', $admin),
            ConflictException::class,
            'calculating_not_available',
            'calculate',
        );
    }

    public function test_accepted_hunter_cannot_calculate_before_the_allowed_status(): void
    {
        $hunter = $this->userWithRole(Role::CUSTOMER);
        $bookingId = $this->booking($this->hotel(null), Booking::PROCESSING, Booking::BookingTypeHotel);
        $this->invitation($bookingId, $hunter->id, BookingHunterInvitation::STATUS_ACCEPTED);

        $this->actingAs($hunter);

        $this->assertDenied(
            fn () => $this->calculating->getByCode('BK-CALC', $hunter),
            ConflictException::class,
            'calculating_not_available',
            'calculate',
        );
    }

    public function test_allowed_status_still_stops_when_nobody_has_paid(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);
        $this->booking($this->hotel($admin->id), Booking::PAID, Booking::BookingTypeHotel);

        $this->actingAs($admin);

        $this->assertDenied(
            fn () => $this->calculating->getByCode('BK-CALC', $admin),
            ConflictException::class,
            'no_paid_participants',
            'calculate',
        );
    }

    private function assertDenied(callable $callback, string $exception, string $errorCode, string $domain): void
    {
        try {
            $callback();
            $this->fail('Ожидалась ошибка '.$errorCode);
        } catch (ForbiddenException|ConflictException $caught) {
            $this->assertInstanceOf($exception, $caught);
            $this->assertSame($errorCode, $caught->getErrorCode());
            $this->assertSame($domain, $caught->getDomain());
        }
    }

    private function prepareDatabase(): void
    {
        Schema::dropIfExists('bc_booking_services');
        Schema::dropIfExists('bc_booking_hunter_invitations');
        Schema::dropIfExists('bc_booking_hunters');
        Schema::dropIfExists('bc_bookings');
        Schema::dropIfExists('bc_hotels');
        Schema::dropIfExists('users');
        Schema::dropIfExists('core_roles');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->string('user_name')->nullable()->unique();
            $table->string('status', 20)->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('core_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('bc_hotels', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('admin_base')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('hotel_id')->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_booking_hunters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->boolean('is_master')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_booking_hunter_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_hunter_id');
            $table->unsignedBigInteger('hunter_id')->nullable();
            $table->string('status')->nullable();
            $table->boolean('prepayment_paid')->default(false);
            $table->string('prepayment_paid_status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_booking_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('service_type')->nullable();
            $table->timestamps();
        });
    }

    private function userWithRole(string $code): User
    {
        Role::query()->firstOrCreate(
            ['code' => $code],
            ['name' => $code],
        );

        $user = User::factory()->create();
        $user->assignRole($code);

        return $user->fresh();
    }

    private function hotel(?int $adminId): int
    {
        return DB::table('bc_hotels')->insertGetId([
            'slug' => 'base-'.($adminId ?? 'none').'-'.uniqid(),
            'title' => 'База',
            'status' => 'publish',
            'admin_base' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function booking(int $hotelId, string $status, string $type = Booking::BookingTypeHotel): int
    {
        return DB::table('bc_bookings')->insertGetId([
            'code' => 'BK-CALC',
            'status' => $status,
            'type' => $type,
            'hotel_id' => $hotelId,
            'total' => 1000,
            'is_paid' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function invitation(int $bookingId, int $hunterId, string $status): void
    {
        $masterId = DB::table('bc_booking_hunters')->insertGetId([
            'booking_id' => $bookingId,
            'invited_by' => $hunterId + 100,
            'is_master' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('bc_booking_hunter_invitations')->insert([
            'booking_hunter_id' => $masterId,
            'hunter_id' => $hunterId,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
