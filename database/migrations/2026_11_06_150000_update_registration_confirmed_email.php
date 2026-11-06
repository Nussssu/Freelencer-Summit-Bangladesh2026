<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;

/**
 * The "Registration confirmed" email gets its full wording (reference, details table, next steps). A template still at
 * the first default is upgraded; one the team already edited keeps their wording. A missing one is created.
 */
return new class extends Migration
{
    private const OLD_BODY = "Your registration for {site} is confirmed.\n\nYour reference number is **{reference}**. Bring it (on your phone is fine) to check in on the day.\n\nWe will email the final venue, timings and your e-ticket before the event.";

    public function up(): void
    {
        $template = EmailTemplate::firstOrNew(['key' => 'registration_confirmed'], ['name' => 'Registration confirmed', 'enabled' => true]);
        if (! $template->exists || $template->body === self::OLD_BODY) {
            $template->fill([
                'subject' => EmailTemplate::REGISTRATION_SUBJECT,
                'heading' => EmailTemplate::REGISTRATION_HEADING,
                'body' => EmailTemplate::REGISTRATION_BODY,
            ])->save();
        }
    }

    public function down(): void
    {
        // Content migration: nothing to undo.
    }
};
