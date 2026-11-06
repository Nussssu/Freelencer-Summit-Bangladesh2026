<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\NewsPostResource;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsPosts extends ManageRecords
{
    protected static string $resource = NewsPostResource::class;

    protected function getHeaderActions(): array
    {
        return [NewsPostResource::createAction()];
    }
}
