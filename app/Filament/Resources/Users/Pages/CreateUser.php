<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Throwable;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Invite user';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['password' => Str::random(48)]; // unusable until they set their own from the invite
    }

    protected function afterCreate(): void
    {
        try {
            UserResource::invite($this->record);
            Notification::make()->success()->title("Invitation sent to {$this->record->email}")->send();
        } catch (Throwable $e) {
            Notification::make()->warning()->title('User created, but the invitation email failed')->body($e->getMessage().' Use "Send invite" once email works.')->persistent()->send();
        }
    }
}
