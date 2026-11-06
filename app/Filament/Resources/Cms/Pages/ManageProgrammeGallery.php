<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\ProgrammeGalleryResource;
use Filament\Resources\Pages\ManageRecords;

class ManageProgrammeGallery extends ManageRecords
{
    protected static string $resource = ProgrammeGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [ProgrammeGalleryResource::createAction()];
    }
}
