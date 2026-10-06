<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait PreparesHotelSearchDatabase
{
    protected function prepareHotelSearchDatabase(): void
    {
        Schema::dropIfExists('bc_hotel_room_dates');
        Schema::dropIfExists('bc_hotel_animals');
        Schema::dropIfExists('bc_hotel_hunting_methods');
        Schema::dropIfExists('bc_hunting_methods');
        Schema::dropIfExists('bc_hotel_rooms');
        Schema::dropIfExists('bc_animals');
        Schema::dropIfExists('bc_hotels');
        Schema::dropIfExists('bc_locations');
        Schema::dropIfExists('users');
        Schema::dropIfExists('core_roles');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
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

        Schema::create('bc_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('_lft')->default(0);
            $table->unsignedInteger('_rgt')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_hotels', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedTinyInteger('star_rate')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('admin_base')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('number')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_hotel_room_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_id');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('number')->nullable();
            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedTinyInteger('is_instant')->default(0);
            $table->timestamps();
        });

        Schema::create('bc_animals', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bc_hotel_animals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('animal_id');
            $table->string('status')->nullable();
        });

        Schema::create('bc_hunting_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('bc_hotel_hunting_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('hunting_method_id');
        });
    }
}
