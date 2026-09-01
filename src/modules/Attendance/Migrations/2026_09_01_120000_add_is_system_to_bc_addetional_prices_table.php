<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Attendance\Models\AddetionalPrice;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_addetional_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('bc_addetional_prices', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('type');
            }
        });

        DB::table('bc_addetional_prices')
            ->where(function ($query) {
                $query
                    ->where('type', AddetionalPrice::FOOD)
                    ->orWhere('name', AddetionalPrice::FOOD_NAME);
            })
            ->update(['is_system' => true]);
    }

    public function down(): void
    {
        Schema::table('bc_addetional_prices', function (Blueprint $table) {
            if (Schema::hasColumn('bc_addetional_prices', 'is_system')) {
                $table->dropColumn('is_system');
            }
        });
    }
};
