<?php

use App\Support\Access;
use Illuminate\Database\Migrations\Migration;

/**
 * Permissions for the new admin modules (memberships, awards, sponsorships, messages, programmes, agenda, speakers,
 * news, stories, partners, exhibitors, downloads). Existing roles get each new module's defaults once; grants an
 * admin already changed are kept. Safe to run again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Access::sync();
    }

    public function down(): void
    {
        // Permissions are left in place: removing them would silently strip access from roles.
    }
};
