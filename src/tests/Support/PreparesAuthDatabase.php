<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait PreparesAuthDatabase
{
    protected function prepareAuthDatabase(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('bc_user_weapons');
        Schema::dropIfExists('bc_hotel_rooms');
        Schema::dropIfExists('bc_animals');
        Schema::dropIfExists('bc_hotels');
        Schema::dropIfExists('users');
        Schema::dropIfExists('core_roles');
        Schema::dropIfExists('core_settings');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('patronymic')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->string('user_name')->nullable()->unique();
            $table->string('status', 20)->nullable();
            $table->string('locale', 10)->nullable();
            $table->text('current_password')->nullable();
            $table->unsignedBigInteger('avatar_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('core_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('core_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('group', 50)->nullable();
            $table->text('val')->nullable();
            $table->timestamps();
        });

        Schema::create('bc_user_weapons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('weapon_type_id')->nullable();
            $table->string('caliber_id')->nullable();
            $table->timestamps();
        });

        Schema::create('bc_hotels', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('bc_hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('bc_animals', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('status')->nullable();
        });
    }
}
