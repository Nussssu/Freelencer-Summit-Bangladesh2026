<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category', 24)->index(); // PartnerCategory
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });

        Schema::create('sponsor_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->json('benefits')->nullable();
            $table->boolean('provisional')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->boolean('confirmed')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });

        Schema::create('booths', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('status', 16)->default('available'); // BoothStatus
            $table->string('size_label', 32)->nullable();
            $table->decimal('x', 7, 2); // SVG user units on the floor-plan viewBox
            $table->decimal('y', 7, 2);
            $table->decimal('w', 7, 2);
            $table->decimal('h', 7, 2);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });

        Schema::create('exhibitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booth_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('sector', 64)->index();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->boolean('visible')->default(true);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['exhibitors', 'booths', 'sponsors', 'sponsor_packages', 'partners'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
