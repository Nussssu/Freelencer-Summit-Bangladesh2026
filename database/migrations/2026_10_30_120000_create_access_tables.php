<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
        });

        // Who did what in the CMS. Changed field names only, never values (some fields are encrypted or personal).
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30)->index();
            $table->string('module', 60)->index();
            $table->string('description', 500);
            $table->nullableMorphs('subject');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['suspended_at', 'last_login_at']));
    }
};
