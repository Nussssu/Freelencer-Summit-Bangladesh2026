<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Mail\LeadMail;
use App\Models\EmailTemplate;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')->label('Send test to me')->icon('heroicon-o-paper-airplane')->color('gray')
                ->modalDescription('Save first: the test uses the saved template with sample data.')
                ->requiresConfirmation()
                ->action(function (EmailTemplate $record) {
                    $sample = new Lead(['type' => 'summit_registration', 'reference' => 'FS-TEST-0001', 'name' => 'Sample Person', 'email' => auth()->user()->email,
                        'data' => ['name' => 'Sample Person', 'email' => auth()->user()->email, 'phone' => '+8801700000000', 'institution' => 'University of Dhaka']]);
                    try {
                        Mail::to(auth()->user()->email)->sendNow(new LeadMail($sample, $record));
                        Notification::make()->success()->title('Test sent to '.auth()->user()->email)->send();
                    } catch (Throwable $e) {
                        Notification::make()->danger()->title('Sending failed')->body($e->getMessage())->persistent()->send();
                    }
                }),
        ];
    }
}
