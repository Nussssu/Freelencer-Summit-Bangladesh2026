<?php

namespace App\Filament\Resources\Cms;

use App\Enums\ProgramType;
use App\Filament\Resources\Cms\Pages\ManageCampusPrograms;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class CampusProgramResource extends ProgramResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Campus programs';

    protected static ?string $pluralModelLabel = 'Campus programs';

    protected static ?string $modelLabel = 'campus program';

    protected static ?int $navigationSort = 4;

    public static function type(): ProgramType
    {
        return ProgramType::CampusProgram;
    }

    public static function getPages(): array
    {
        return ['index' => ManageCampusPrograms::route('/')];
    }
}
