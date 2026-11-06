<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\VenueResource;
use Filament\Resources\Pages\ManageRecords;

class ManageVenues extends ManageRecords
{
    protected static string $resource = VenueResource::class;

    protected function getHeaderActions(): array
    {
        return [VenueResource::createAction()];
    }
}
