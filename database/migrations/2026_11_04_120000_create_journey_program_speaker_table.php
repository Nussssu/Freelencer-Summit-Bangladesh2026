<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Speakers and mentors at a journey programme (roadshow, divisional summit, campus program), shown on its detail page.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_program_speaker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speaker_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->unique(['journey_program_id', 'speaker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_program_speaker');
    }
};
