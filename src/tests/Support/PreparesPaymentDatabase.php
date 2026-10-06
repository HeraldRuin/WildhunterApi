<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait PreparesPaymentDatabase
{
    protected function preparePaymentDatabase(): void
    {
        Schema::dropIfExists('bc_booking_meta');
        Schema::dropIfExists('bc_hotel_room_bookings');
        Schema::dropIfExists('bc_booking_payments');
        Schema::dropIfExists('bc_booking_hunter_invitations');
        Schema::dropIfExists('bc_booking_hunters');
        Schema::dropIfExists('bc_bookings');

        Schema::create('bc_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('object_id')->nullable();
            $table->unsignedBigInteger('hotel_id')->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->boolean('prepayment_paid')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_booking_hunters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->boolean('is_master')->default(false);
            $table->string('creator_role')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_booking_hunter_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_hunter_id');
            $table->unsignedBigInteger('hunter_id')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->boolean('prepayment_paid')->default(false);
            $table->string('prepayment_paid_status')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_booking_payments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('object_id')->nullable();
            $table->string('object_model')->nullable();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('create_user')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('invoice_id')->nullable();
            $table->string('status');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 8)->nullable();
            $table->string('payment_url')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('next_check_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('logs')->nullable();
            $table->timestamps();
        });

        Schema::create('bc_booking_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('name');
            $table->text('val')->nullable();
            $table->timestamps();
        });

        Schema::create('bc_hotel_room_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
        });
    }
}
