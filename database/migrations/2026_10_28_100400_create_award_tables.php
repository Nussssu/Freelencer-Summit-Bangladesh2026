<?php

use App\Support\SubmissionColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('award_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('eligibility')->nullable();
            $table->json('criteria')->nullable();
            $table->date('deadline')->nullable();
            $table->boolean('is_open')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('award_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('award_category_id')->constrained();
            $table->string('edition', 4)->index();
            $table->boolean('self_nominated');
            $table->string('nominator_name')->nullable();
            $table->string('nominator_email')->nullable();
            $table->text('nominator_phone')->nullable(); // encrypted
            $table->string('nominee_name');
            $table->string('nominee_email');
            $table->text('nominee_phone'); // encrypted
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->json('portfolio_links')->nullable();
            $table->json('marketplace_profiles')->nullable();
            $table->text('achievements')->nullable();
            $table->string('status', 24)->default('submitted'); // AwardStatus
            $table->boolean('shortlist_published')->default(false);
            $table->boolean('winner_published')->default(false);
            SubmissionColumns::add($table);
            $table->index(['status', 'shortlist_published', 'winner_published'], 'award_apps_public_idx');
        });

        Schema::create('award_application_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('award_application_id')->constrained()->cascadeOnDelete();
            $table->string('path'); // private disk, random name
            $table->string('original_name');
            $table->string('mime', 64);
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['award_application_files', 'award_applications', 'award_categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
