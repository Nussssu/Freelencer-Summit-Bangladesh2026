<?php

namespace App\Filament\Resources\Cms;

use App\Enums\ProgramType;
use App\Filament\Resources\Cms\Pages\ManageDivisionalSummits;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class DivisionalSummitResource extends ProgramResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationLabel = 'Divisional summits';

    protected static ?string $pluralModelLabel = 'Divisional summits';

    protected static ?string $modelLabel = 'divisional summit';

    protected static ?int $navigationSort = 2;

    public static function type(): ProgramType
    {
        return ProgramType::DivisionalSummit;
    }

    public static function getPages(): array
    {
        return ['index' => ManageDivisionalSummits::route('/')];
    }
}
