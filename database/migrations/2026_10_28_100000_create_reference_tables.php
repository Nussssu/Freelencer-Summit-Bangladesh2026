<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->timestamps();
        });

        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('institution_type', 32)->index(); // university, polytechnic, college
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
        });

        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->timestamps();
        });

        Schema::create('halls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('reference_sequences', function (Blueprint $table) {
            $table->string('key', 32)->primary();
            $table->unsignedInteger('last')->default(0);
        });
    }

    public function down(): void
    {
        foreach (['reference_sequences', 'halls', 'venues', 'campuses', 'districts', 'divisions'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
