<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reported attendance of a held programme: the national journey's "participants reached" and division cards. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journey_programs', function (Blueprint $t) {
            $t->unsignedInteger('participants')->nullable(); // null = not reported yet (registrations are counted instead)
        });
    }

    public function down(): void
    {
        Schema::table('journey_programs', fn (Blueprint $t) => $t->dropColumn('participants'));
    }
};
