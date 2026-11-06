<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Schemas\LeadForm;
use App\Filament\Resources\Leads\Tables\LeadsTable;
use App\Models\Lead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LeadResource extends Resource
{
    use ChecksModulePermission;

    protected static string $module = 'leads';

    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|UnitEnum|null $navigationGroup = 'Leads & CRM';

    protected static ?string $navigationLabel = 'All leads';

    protected static ?string $pluralModelLabel = 'Leads';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return ($n = Lead::query()->where('status', 'new')->count()) ? (string) $n : null;
    }

    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
