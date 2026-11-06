<?php

namespace App\Filament\Resources\Cms;

use App\Enums\ProgramType;
use App\Filament\Resources\Cms\Pages\ManageDistrictRoadshows;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class DistrictRoadshowResource extends ProgramResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $navigationLabel = 'District roadshows';

    protected static ?string $pluralModelLabel = 'District roadshows';

    protected static ?string $modelLabel = 'district roadshow';

    protected static ?int $navigationSort = 3;

    public static function type(): ProgramType
    {
        return ProgramType::DistrictRoadshow;
    }

    public static function getPages(): array
    {
        return ['index' => ManageDistrictRoadshows::route('/')];
    }
}
