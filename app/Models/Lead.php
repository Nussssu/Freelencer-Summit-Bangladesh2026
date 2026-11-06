<?php

namespace App\Models;

use App\Mail\LeadMail;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/** One row per public form submission (any form), plus the admin and user emails it triggers. */
class Lead extends Model
{
    protected $guarded = ['id'];

    /** While true (CSV imports), new leads are recorded without sending any email. */
    public static bool $quiet = false;

    public const STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed', 'spam' => 'Spam'];

    /** Lead types confirmed with the "Registration confirmed" template instead of "Enquiry received". */
    private const REGISTRATIONS = ['summit_registration', 'program_registration'];

    // Never copied into the lead or its emails.
    private const HIDDEN = ['id', 'ip_hash', 'utm', 'consent_version', 'deleted_at', 'updated_at', 'created_at', 'password'];

    protected function casts(): array
    {
        return ['data' => 'encrypted:array', 'phone' => 'encrypted', 'follow_up_at' => 'datetime']; // source tables encrypt phones; the copy must too
    }

    public function submittable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isRegistration(): bool
    {
        return in_array($this->type, self::REGISTRATIONS, true);
    }

    public function typeLabel(): string
    {
        return Str::headline($this->type);
    }

    public static function capture(Model $submission): self
    {
        $data = array_diff_key($submission->attributesToArray(), array_flip(self::HIDDEN));
        $lead = static::create([
            'type' => Str::snake(class_basename($submission)),
            'reference' => $data['reference'] ?? null,
            'name' => $data['name'] ?? $data['full_name'] ?? $data['contact_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'data' => $data,
            'submittable_type' => $submission->getMorphClass(),
            'submittable_id' => $submission->getKey(),
        ]);
        if (! static::$quiet) {
            rescue(fn () => $lead->notify()); // a mail or queue fault must never fail the visitor's submission
        }

        return $lead;
    }

    /** Admin summary to the configured inbox(es), branded confirmation to the person who submitted. */
    public function notify(): void
    {
        $templates = EmailTemplate::query()->where('enabled', true)->get()->keyBy('key');
        $admins = array_filter(Settings::get('mail', [])['admin_emails'] ?? []);

        if ($admins && $templates->has('admin_notification')) {
            Mail::to($admins)->queue(new LeadMail($this, $templates['admin_notification']));
        }
        $confirmation = $this->isRegistration() && $templates->has('registration_confirmed')
            ? $templates['registration_confirmed'] : $templates->get('user_confirmation');
        if (! $this->email || ! $confirmation) {
            return;
        }
        if ($this->isRegistration()) {
            // Registration confirmations go out right after the response (no queue worker needed), so they always arrive.
            $mail = new LeadMail($this, $confirmation);
            app()->terminating(fn () => rescue(fn () => Mail::to($this->email)->sendNow($mail)));

            return;
        }
        Mail::to($this->email)->queue(new LeadMail($this, $confirmation));
    }
}
