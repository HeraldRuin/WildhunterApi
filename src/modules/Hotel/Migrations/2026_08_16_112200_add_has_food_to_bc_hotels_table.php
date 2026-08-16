<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('bc_hotels', 'has_food')) {
                $table->boolean('has_food')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            if (Schema::hasColumn('bc_hotels', 'has_food')) {
                $table->dropColumn('has_food');
            }
        });
    }
};
