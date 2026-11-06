<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageLogResource;
use Filament\Resources\Pages\ListRecords;

class ListMessageLogs extends ListRecords
{
    protected static string $resource = MessageLogResource::class;
}
