<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\SponsorResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSponsors extends ManageRecords
{
    protected static string $resource = SponsorResource::class;

    protected function getHeaderActions(): array
    {
        return [SponsorResource::createAction()];
    }
}
