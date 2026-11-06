<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;

/** The "Forum membership approved" email, sent when the team clicks Approve in Registrations › Forum memberships. */
return new class extends Migration
{
    public function up(): void
    {
        EmailTemplate::firstOrCreate(['key' => 'forum_approved'], [...EmailTemplate::FORUM_APPROVED, 'enabled' => true]);
    }

    public function down(): void
    {
        EmailTemplate::where('key', 'forum_approved')->delete();
    }
};
