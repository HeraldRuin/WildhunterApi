<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bc_booking_service_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('booking_service_id')->nullable();
            $table->string('action');
            $table->string('service_type')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('title')->nullable();
            $table->string('calculation_type')->nullable();
            $table->unsignedInteger('count')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedBigInteger('hunter_id')->nullable();
            $table->string('hunter_name')->nullable();
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->string('animal_title')->nullable();
            $table->text('comment')->nullable();
            $table->string('place')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bc_booking_service_logs');
    }
};
