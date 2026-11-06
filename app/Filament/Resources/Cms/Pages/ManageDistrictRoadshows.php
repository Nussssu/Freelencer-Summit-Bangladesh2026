<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\DistrictRoadshowResource;
use Filament\Resources\Pages\ManageRecords;

class ManageDistrictRoadshows extends ManageRecords
{
    protected static string $resource = DistrictRoadshowResource::class;

    protected function getHeaderActions(): array
    {
        return [DistrictRoadshowResource::createAction()];
    }
}
