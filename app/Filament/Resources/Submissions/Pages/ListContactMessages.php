<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\ContactMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;
}
