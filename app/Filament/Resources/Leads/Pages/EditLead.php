<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')->label('Reply by email')->icon('heroicon-o-envelope')->color('gray')
                ->url(fn (Lead $record) => $record->email ? 'mailto:'.$record->email.'?subject='.rawurlencode('Re: '.$record->typeLabel().' '.$record->reference) : null)
                ->visible(fn (Lead $record) => (bool) $record->email),
            Action::make('resend')->label('Resend emails')->icon('heroicon-o-paper-airplane')->color('gray')->requiresConfirmation()
                ->modalDescription('Sends the admin notification and the user confirmation again.')
                ->action(function (Lead $record) {
                    $record->notify();
                    Notification::make()->success()->title('Emails queued')->send();
                }),
            DeleteAction::make(),
        ];
    }
}
