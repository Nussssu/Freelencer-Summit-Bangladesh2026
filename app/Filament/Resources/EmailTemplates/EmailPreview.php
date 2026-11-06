<?php

namespace App\Filament\Resources\EmailTemplates;

use App\Mail\LeadMail;
use App\Models\EmailTemplate;
use App\Models\Lead;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

/** The saved template rendered with sample data, shown in a modal (Preview) or sent to the signed-in admin (test). */
class EmailPreview
{
    public static function sample(EmailTemplate $template, ?string $email = null): Lead
    {
        $email ??= 'sample.person@example.com';
        $type = match ($template->key) {
            'registration_confirmed' => 'summit_registration',
            'forum_approved' => 'forum_application',
            default => 'contact_message',
        };

        return new Lead(['type' => $type, 'reference' => 'FSB-REG-2026-00001', 'name' => 'Sample Person', 'email' => $email, 'data' => [
            'name' => 'Sample Person', 'email' => $email, 'phone' => '+8801700000000', 'organisation' => 'University of Dhaka',
            'designation' => 'Student', 'attendee_type' => 'freelancer', 'reference' => 'FSB-REG-2026-00001',
        ]]);
    }

    public static function html(EmailTemplate $template): string
    {
        $mail = new LeadMail(self::sample($template), $template);

        return $mail->render();
    }

    public static function action(): Action
    {
        return Action::make('preview')->label('Preview')->icon('heroicon-o-eye')->color('gray')
            ->modalHeading(fn (EmailTemplate $record) => 'Preview: '.$record->name)
            ->modalDescription(fn (EmailTemplate $record) => 'Subject: '.strtr($record->subject, ['{site}' => config('summit.name'), '{reference}' => 'FSB-REG-2026-00001', '{name}' => 'Sample Person', '{type}' => 'Summit Registration', '{email}' => 'sample.person@example.com']).' · Sample data, saved version.')
            ->modalContent(fn (EmailTemplate $record) => new HtmlString('<iframe title="Email preview" sandbox srcdoc="'.e(self::html($record)).'" style="width:100%;height:70vh;border:1px solid rgba(0,0,0,.1);border-radius:.5rem;background:#fff"></iframe>'))
            ->modalWidth('4xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }
}
