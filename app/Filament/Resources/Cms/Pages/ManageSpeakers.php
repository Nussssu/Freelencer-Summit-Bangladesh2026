<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\SpeakerResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSpeakers extends ManageRecords
{
    protected static string $resource = SpeakerResource::class;

    protected function getHeaderActions(): array
    {
        return [SpeakerResource::createAction()];
    }
}
