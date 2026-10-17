<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bc_user_weapons', function (Blueprint $table) {
            $table->string('hunter_license_series')->nullable()->after('hunter_license_number');
        });
    }

    public function down(): void
    {
        Schema::table('bc_user_weapons', function (Blueprint $table) {
            $table->dropColumn('hunter_license_series');
        });
    }
};
