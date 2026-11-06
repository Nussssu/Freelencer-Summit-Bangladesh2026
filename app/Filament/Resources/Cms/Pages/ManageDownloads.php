<?php

namespace App\Filament\Resources\Cms\Pages;

use App\Filament\Resources\Cms\DownloadResource;
use Filament\Resources\Pages\ManageRecords;

class ManageDownloads extends ManageRecords
{
    protected static string $resource = DownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [DownloadResource::createAction()];
    }
}
