<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_programs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 24); // ProgramType
            $table->string('slug')->unique();
            $table->string('title');
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->string('partner')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 24)->default('upcoming'); // ProgramStatus
            $table->boolean('registration_open')->default(false);
            $table->string('cover_image')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
            $table->index(['type', 'status']);
            $table->index('starts_at');
        });

        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('organisation')->nullable();
            $table->string('country', 2)->default('BD');
            $table->string('role_label', 48)->nullable(); // "Keynote speaker", "Panelist" …
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->json('expertise')->nullable();
            $table->json('links')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('visible')->default(true);
            $table->boolean('featured')->default(false);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['visible', 'featured', 'sort']);
        });

        // "sessions" is Laravel's session store, so agenda rows live in agenda_sessions.
        Schema::create('agenda_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->date('date')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->foreignId('hall_id')->nullable()->constrained()->nullOnDelete();
            $table->string('track')->nullable();
            $table->string('type', 32); // SessionType
            $table->foreignId('moderator_id')->nullable()->constrained('speakers')->nullOnDelete();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
            $table->index(['date', 'starts_at']);
            $table->index('type');
        });

        Schema::create('agenda_session_speaker', function (Blueprint $table) {
            $table->foreignId('agenda_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speaker_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->primary(['agenda_session_id', 'speaker_id']);
        });

        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage', 24)->index(); // ProgramType or "grand-summit"
            $table->string('kind', 8); // MediaKind
            $table->string('album')->nullable();
            $table->string('src')->nullable();
            $table->string('youtube_id', 16)->nullable();
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['media_items', 'agenda_session_speaker', 'agenda_sessions', 'speakers', 'journey_programs'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
