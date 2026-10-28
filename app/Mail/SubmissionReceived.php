<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** One queued, branded confirmation for every journey; each caller passes its own subject and lines. */
class SubmissionReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @param list<string> $lines */
    public function __construct(
        public string $heading,
        public string $reference,
        public array $lines,
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
        public ?string $subjectLine = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine ?? "{$this->heading} · {$this->reference}");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.submission');
    }
}
