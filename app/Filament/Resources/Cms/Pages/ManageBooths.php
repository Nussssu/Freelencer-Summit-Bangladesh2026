<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\BoothResource;
use Filament\Resources\Pages\ManageRecords;

class ManageBooths extends ManageRecords
{
    protected static string $resource = BoothResource::class;

    protected function getHeaderActions(): array
    {
        return [BoothResource::createAction()];
    }
}
