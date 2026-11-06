<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\CampusProgramResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCampusPrograms extends ManageRecords
{
    protected static string $resource = CampusProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [CampusProgramResource::createAction()];
    }
}
