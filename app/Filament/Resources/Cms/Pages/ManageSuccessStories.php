<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\SuccessStoryResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSuccessStories extends ManageRecords
{
    protected static string $resource = SuccessStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [SuccessStoryResource::createAction()];
    }
}
