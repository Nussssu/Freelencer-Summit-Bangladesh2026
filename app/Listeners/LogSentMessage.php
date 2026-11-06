<?php

namespace App\Listeners;

use App\Models\Lead;
use App\Models\MessageLog;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Address;
use Throwable;

/**
 * Messages › Email & SMS log: one row per recipient of every email the site sends, linked to the submission (lead)
 * it was about when there is one. Logging never breaks sending.
 */
class LogSentMessage
{
    public function handle(MessageSent $event): void
    {
        rescue(function () use ($event) {
            $lead = $event->data['lead'] ?? null;
            $to = array_map(fn (Address $a) => $a->getAddress(), $event->message->getTo());
            foreach ($to ?: ['(unknown)'] as $recipient) {
                MessageLog::create([
                    'channel' => 'email',
                    'recipient' => mb_substr($recipient, 0, 255),
                    'subject' => mb_substr((string) $event->message->getSubject(), 0, 255) ?: null,
                    'status' => 'sent',
                    'lead_id' => $lead instanceof Lead ? $lead->getKey() : null,
                ]);
            }
        }, report: false);
    }

    /** A queued email that gave up after its retries. */
    public static function failed(?Lead $lead, array $recipients, ?string $subject, Throwable $e): void
    {
        rescue(function () use ($lead, $recipients, $subject, $e) {
            foreach ($recipients ?: ['(unknown)'] as $recipient) {
                MessageLog::create([
                    'channel' => 'email', 'recipient' => mb_substr((string) $recipient, 0, 255), 'subject' => $subject ? mb_substr($subject, 0, 255) : null,
                    'status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 2000), 'lead_id' => $lead?->getKey(),
                ]);
            }
        }, report: false);
    }
}
