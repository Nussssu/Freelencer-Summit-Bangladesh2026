<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\ExhibitorResource;
use Filament\Resources\Pages\ManageRecords;

class ManageExhibitors extends ManageRecords
{
    protected static string $resource = ExhibitorResource::class;

    protected function getHeaderActions(): array
    {
        return [ExhibitorResource::createAction()];
    }
}
