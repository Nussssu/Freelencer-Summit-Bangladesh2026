<?php

namespace App\Filament\Resources\EmailTemplates;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Filament\Resources\EmailTemplates\Schemas\EmailTemplateForm;
use App\Filament\Resources\EmailTemplates\Tables\EmailTemplatesTable;
use App\Models\EmailTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** The two lead emails (admin summary, user confirmation): editable, never created or deleted. */
class EmailTemplateResource extends Resource
{
    use ChecksModulePermission;

    protected static string $module = 'email_templates';

    protected static ?string $model = EmailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Messages';

    protected static ?string $navigationLabel = 'Email templates';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmailTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailTemplatesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailTemplates::route('/'),
            'edit' => EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
