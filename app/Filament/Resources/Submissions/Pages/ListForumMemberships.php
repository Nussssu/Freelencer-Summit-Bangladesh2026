<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\ForumMembershipResource;
use Filament\Resources\Pages\ListRecords;

class ListForumMemberships extends ListRecords
{
    protected static string $resource = ForumMembershipResource::class;
}
