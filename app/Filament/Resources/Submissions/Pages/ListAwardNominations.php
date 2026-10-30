<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\AwardNominationResource;
use Filament\Resources\Pages\ListRecords;

class ListAwardNominations extends ListRecords
{
    protected static string $resource = AwardNominationResource::class;
}
