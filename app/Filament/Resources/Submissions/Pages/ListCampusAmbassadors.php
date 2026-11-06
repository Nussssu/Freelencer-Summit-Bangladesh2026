<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\CampusAmbassadorResource;
use Filament\Resources\Pages\ListRecords;

class ListCampusAmbassadors extends ListRecords
{
    protected static string $resource = CampusAmbassadorResource::class;

    public function getTabs(): array
    {
        return CampusAmbassadorResource::tabs();
    }
}
