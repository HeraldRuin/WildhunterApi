<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_hotel_animals', function (Blueprint $table) {
            if (!Schema::hasColumn('bc_hotel_animals', 'max_hunters_count')) {
                $table->unsignedInteger('max_hunters_count')->nullable()->after('hunters_count');
            }
        });

        if (Schema::hasColumn('bc_hotel_animals', 'max_hunters_count')) {
            DB::table('bc_hotel_animals')
                ->whereNull('max_hunters_count')
                ->update([
                    'max_hunters_count' => DB::raw('GREATEST(COALESCE(hunters_count, 1), 1)'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('bc_hotel_animals', function (Blueprint $table) {
            if (Schema::hasColumn('bc_hotel_animals', 'max_hunters_count')) {
                $table->dropColumn('max_hunters_count');
            }
        });
    }
};
