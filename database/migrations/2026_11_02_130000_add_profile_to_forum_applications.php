<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The forum form's personal profile step: nationality, optional NID (encrypted), profile type. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_applications', function (Blueprint $table) {
            $table->string('nationality', 24)->nullable()->after('district_id'); // SubmissionController::NATIONALITIES
            $table->text('nid')->nullable()->after('nationality'); // encrypted
            $table->string('profile_type', 24)->nullable()->after('nid'); // SubmissionController::PROFILE_TYPES
        });
    }

    public function down(): void
    {
        Schema::table('forum_applications', function (Blueprint $table) {
            $table->dropColumn(['nationality', 'nid', 'profile_type']);
        });
    }
};
