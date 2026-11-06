<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\DivisionalSummitResource;
use Filament\Resources\Pages\ManageRecords;

class ManageDivisionalSummits extends ManageRecords
{
    protected static string $resource = DivisionalSummitResource::class;

    protected function getHeaderActions(): array
    {
        return [DivisionalSummitResource::createAction()];
    }
}
