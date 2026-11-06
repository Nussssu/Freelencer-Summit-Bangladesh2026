<?php

namespace App\Mail;

use App\Listeners\LogSentMessage;
use App\Models\EmailTemplate;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;

/** A lead email built from an admin-edited template (admin summary or user confirmation). */
class LeadMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead, public EmailTemplate $template) {}

    /** Called by the queue when sending gave up: recorded in Messages › Email & SMS log. */
    public function failed(Throwable $e): void
    {
        LogSentMessage::failed($this->lead, array_column($this->to, 'address'), $this->fill($this->template->subject, escape: false), $e);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->fill($this->template->subject, escape: false));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.lead', with: [
            'heading' => $this->fill($this->template->heading, escape: false),
            // Markdown first (admin-authored), then placeholders with escaped visitor input, so submitted text can't inject HTML.
            'body' => new HtmlString($this->fill(Str::markdown($this->template->body, ['html_input' => 'strip']))),
        ]);
    }

    private function fill(string $text, bool $escape = true): string
    {
        $e = fn ($v) => $escape ? e((string) $v) : (string) $v;
        $values = [
            '{name}' => $e($this->lead->name ?? 'there'),
            '{email}' => $e($this->lead->email),
            '{type}' => $e($this->lead->typeLabel()),
            '{reference}' => $e($this->lead->reference ?? '#'.$this->lead->id),
            '{site}' => $e(config('summit.name')),
            '{fields}' => $escape ? $this->fieldsTable() : '',
            '{details}' => $escape ? $this->detailsTable() : '',
        ];

        return strtr($text, $values);
    }

    /** The visitor's own key details (for confirmations): name, contact, organisation, attendee type, reference. */
    private function detailsTable(): string
    {
        $data = (array) $this->lead->data;
        $labels = ['name' => 'Name', 'email' => 'Email', 'phone' => 'Mobile', 'organisation' => 'Organisation', 'institution' => 'Institution',
            'designation' => 'Designation', 'attendee_type' => 'Attendee type', 'reference' => 'Reference'];
        $rows = collect($labels)
            ->map(fn ($label, $k) => [$label, $data[$k] ?? ($k === 'reference' ? $this->lead->reference : null)])
            ->filter(fn ($r) => is_scalar($r[1]) && (string) $r[1] !== '')
            ->map(fn ($r, $k) => '<tr><td style="padding:6px 12px 6px 0;color:#3f4b44;white-space:nowrap">'.e($r[0]).'</td><td style="padding:6px 0;font-weight:600">'.e($k === 'attendee_type' ? Str::headline((string) $r[1]) : (string) $r[1]).'</td></tr>')
            ->implode('');

        return '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-top:1px solid rgba(7,20,17,.1);font-size:14px">'.$rows.'</table>';
    }

    private function fieldsTable(): string
    {
        $rows = collect($this->lead->data)
            ->reject(fn ($v) => $v === null || $v === '')
            ->map(fn ($v, $k) => '<tr><td style="padding:6px 12px 6px 0;color:#3f4b44;vertical-align:top;white-space:nowrap">'.e(Str::headline($k)).'</td><td style="padding:6px 0">'.e(is_scalar($v) ? (string) $v : json_encode($v)).'</td></tr>')
            ->implode('');

        return '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-top:1px solid rgba(7,20,17,.1);font-size:14px">'.$rows.'</table>';
    }
}
