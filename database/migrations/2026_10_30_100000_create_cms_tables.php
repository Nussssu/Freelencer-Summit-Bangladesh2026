<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Media Library: every file lives on the `media` disk (public/media), so existing /media/... URLs stay valid.
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique(); // relative to public/media
            $table->string('name');
            $table->string('alt')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();
        });

        // Per-page SEO on the existing pages table (one row per routed path).
        Schema::table('pages', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('title');
            $table->string('meta_description', 320)->nullable()->after('meta_title');
            $table->string('keywords')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('keywords'); // media path
        });

        // One row per public form submission, whatever the form.
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('type', 60)->index();
            $table->string('reference', 40)->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->json('data');
            $table->string('status', 20)->default('new')->index();
            $table->text('notes')->nullable();
            $table->nullableMorphs('submittable');
            $table->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique(); // admin_notification | user_confirmation
            $table->string('name');
            $table->string('subject');
            $table->string('heading');
            $table->text('body'); // Markdown with {placeholders}
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('leads');
        Schema::table('pages', fn (Blueprint $table) => $table->dropColumn(['meta_title', 'meta_description', 'keywords', 'og_image']));
        Schema::dropIfExists('media');
    }
};
