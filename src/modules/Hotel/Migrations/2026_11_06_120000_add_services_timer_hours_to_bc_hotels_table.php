<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            if (!Schema::hasColumn('bc_hotels', 'services_timer_hours')) {
                $table->integer('services_timer_hours')->nullable()->default(24);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bc_hotels', function (Blueprint $table) {
            if (Schema::hasColumn('bc_hotels', 'services_timer_hours')) {
                $table->dropColumn('services_timer_hours');
            }
        });
    }
};
