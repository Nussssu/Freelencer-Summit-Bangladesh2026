<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exhibit_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('website')->nullable();
            $table->string('industry');
            $table->string('head_office');
            $table->string('full_name');
            $table->string('designation');
            $table->string('email');
            $table->string('phone', 32);
            $table->json('utm')->nullable();
            $table->string('status', 24)->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exhibit_enquiries');
    }
};
