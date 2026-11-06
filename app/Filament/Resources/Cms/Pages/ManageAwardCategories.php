<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\AwardCategoryResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAwardCategories extends ManageRecords
{
    protected static string $resource = AwardCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [AwardCategoryResource::createAction()];
    }
}
