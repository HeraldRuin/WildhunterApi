<?php

namespace Tests\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\Payment;

trait SeedsPrepayment
{
    /**
     * @param  list<User>  $hunters
     */
    protected function seedPrepaymentBooking(array $hunters, float $total = 1000, string $code = 'BK-PAY', string $status = Booking::PREPAYMENT_COLLECTION): int
    {
        $bookingId = DB::table('bc_bookings')->insertGetId([
            'code' => $code,
            'status' => $status,
            'type' => 'hotel',
            'object_id' => 1,
            'total' => $total,
            'prepayment_paid' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $masterId = DB::table('bc_booking_hunters')->insertGetId([
            'booking_id' => $bookingId,
            'is_master' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($hunters as $hunter) {
            DB::table('bc_booking_hunter_invitations')->insert([
                'booking_hunter_id' => $masterId,
                'hunter_id' => $hunter->id,
                'email' => $hunter->email,
                'status' => BookingHunterInvitation::STATUS_ACCEPTED,
                'prepayment_paid' => 0,
                'prepayment_paid_status' => BookingHunterInvitation::PREPAYMENT_PENDING,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $bookingId;
    }

    protected function makeProcessingPayment(int $bookingId, User $hunter, array $overrides = []): Payment
    {
        return Payment::query()->create(array_merge([
            'booking_id' => $bookingId,
            'object_id' => 1,
            'object_model' => 'hotel',
            'user_id' => $hunter->id,
            'create_user' => $hunter->id,
            'payment_gateway' => 'paykeeper',
            'invoice_id' => 'inv-'.$hunter->id,
            'status' => Payment::STATUS_PROCESSING,
            'amount' => 500,
            'currency' => 'RUB',
            'payment_url' => 'https://pay.example/inv-'.$hunter->id,
            'expires_at' => now()->addMinutes(30),
            'attempts' => 0,
        ], $overrides));
    }
}
