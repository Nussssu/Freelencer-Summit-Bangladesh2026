<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A story's type is the /success-stories filter value ("Freelancer", "Entrepreneur", "Student"), longer than 8. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('success_stories', fn (Blueprint $t) => $t->string('kind', 32)->change());
    }

    public function down(): void
    {
        Schema::table('success_stories', fn (Blueprint $t) => $t->string('kind', 8)->change());
    }
};
