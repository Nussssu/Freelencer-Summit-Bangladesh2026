<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\AgendaSessionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAgendaSessions extends ManageRecords
{
    protected static string $resource = AgendaSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [AgendaSessionResource::createAction()];
    }
}
