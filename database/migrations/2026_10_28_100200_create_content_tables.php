<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });

        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category', 32); // NewsCategory
            $table->string('title');
            $table->string('excerpt', 320)->nullable();
            $table->longText('body')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
            $table->index(['category', 'published_at']);
        });

        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('kind', 8); // profile | video
            $table->string('name');
            $table->string('headline');
            $table->text('body')->nullable();
            $table->string('photo')->nullable();
            $table->string('youtube_id', 16)->nullable();
            $table->boolean('published')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });

        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 24)->unique(); // DownloadKind: one current file per kind
            $table->string('title');
            $table->string('file_path')->nullable(); // null = disabled card
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('page', 48)->index(); // route key, e.g. "awards", "register"
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->json('sections')->nullable(); // [{heading, body|null}]
            $table->boolean('is_draft')->default(true);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['site_settings', 'pages', 'faqs', 'downloads', 'success_stories', 'news_posts', 'announcements'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
