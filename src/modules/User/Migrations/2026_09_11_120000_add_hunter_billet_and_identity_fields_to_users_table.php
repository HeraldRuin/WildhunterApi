<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('hunter_billet_issuing_authority')->nullable()->after('hunter_billet_number');
            $table->string('hunter_billet_rf_subject')->nullable()->after('hunter_billet_issuing_authority');
            $table->date('hunter_billet_issue_date')->nullable()->after('hunter_billet_rf_subject');
            $table->string('identity_document')->nullable()->after('hunter_billet_issue_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'hunter_billet_issuing_authority',
                'hunter_billet_rf_subject',
                'hunter_billet_issue_date',
                'identity_document',
            ]);
        });
    }
};
