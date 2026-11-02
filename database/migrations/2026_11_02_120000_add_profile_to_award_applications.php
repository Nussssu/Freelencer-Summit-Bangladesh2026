<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The nomination form's applicant profile step: professional headline, years of freelancing, short bio. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('award_applications', function (Blueprint $table) {
            $table->string('headline', 160)->nullable()->after('district_id');
            $table->string('experience', 16)->nullable()->after('headline'); // SubmissionController::AWARD_EXPERIENCE
            $table->text('bio')->nullable()->after('experience');
        });
    }

    public function down(): void
    {
        Schema::table('award_applications', function (Blueprint $table) {
            $table->dropColumn(['headline', 'experience', 'bio']);
        });
    }
};
