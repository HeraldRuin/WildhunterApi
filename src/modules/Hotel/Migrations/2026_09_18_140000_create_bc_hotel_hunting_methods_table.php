<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bc_hotel_hunting_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotel_id');
            $table->unsignedBigInteger('hunting_method_id');
            $table->unique(['hotel_id', 'hunting_method_id']);
            $table->index('hotel_id');
            $table->index('hunting_method_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bc_hotel_hunting_methods');
    }
};
