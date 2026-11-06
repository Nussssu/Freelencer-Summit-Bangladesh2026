<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Admin-editable lead emails: admin_notification and user_confirmation. Bodies are Markdown with {placeholders}. */
class EmailTemplate extends Model
{
    protected $guarded = ['id'];

    public const PLACEHOLDERS = ['{name}', '{email}', '{type}', '{reference}', '{details}', '{fields}', '{site}'];

    /** Wording of the "Registration confirmed" email sent after the summit and programme registration forms. */
    public const REGISTRATION_SUBJECT = 'Registration confirmed: {site} · {reference}';

    public const REGISTRATION_HEADING = 'You are registered, {name}!';

    public const REGISTRATION_BODY = "Dear {name},

Thank you for registering for **{site}**. Your registration has been received and confirmed.

**Your reference number: {reference}**

Please keep this number. Show it (on your phone is fine) at the registration desk to check in on the day.

**Your registration details**

{details}

**What happens next**

- We will email your e-ticket with a QR code, the final venue and the timings before the event.
- Follow the website for the agenda, speakers and updates.
- If any of your details are wrong, simply reply to this email.

We look forward to seeing you there.

Warm regards,  
The {site} team";

    /** Sent to a BACCO Freelancer Forum applicant when the team approves them (Registrations › Forum memberships). */
    public const FORUM_APPROVED = [
        'name' => 'Forum membership approved',
        'subject' => 'Welcome to the BACCO Freelancer Forum · {reference}',
        'heading' => 'Congratulations, {name}!',
        'body' => "Dear {name},\n\nWe are pleased to let you know that your application to join the **BACCO Freelancer Forum** has been **approved**. Welcome to the community!\n\n**Your membership reference: {reference}**\n\n{details}\n\n**What happens next**\n\n- The forum team will contact you with community updates, events and opportunities.\n- Keep your reference number for any follow-up with the team.\n- If any of your details are wrong, simply reply to this email.\n\nWe look forward to growing together.\n\nWarm regards,  \nThe {site} team",
    ];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
