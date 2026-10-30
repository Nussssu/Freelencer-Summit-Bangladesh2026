<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Concerns\ChecksModulePermission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * One public form's submissions: view, correct or delete (soft delete) in modals; never created from the admin.
 * Encrypted columns (phones) are decrypted by the model casts for display and editing only; they are never searchable
 * or exported. Each form's own resource sets the columns, view and edit form.
 */
abstract class SubmissionResource extends Resource
{
    use ChecksModulePermission { getAuthorizationResponse as modulePermission; }

    protected static string|UnitEnum|null $navigationGroup = 'Leads';

    /** Submissions only come from the public forms. */
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        return in_array($action, ['create', 'replicate'], true) ? Response::deny() : static::modulePermission($action, $record);
    }

    /** Global search hits open the table filtered to the record (these resources have no record pages). */
    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('index', ['search' => $record->getAttribute(static::$recordTitleAttribute)]);
    }

    /** @return list<Column> */
    abstract protected static function columns(): array;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Received')->dateTime('d M Y, h:i A')->sortable(),
                ...static::columns(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([ViewAction::make()->iconButton()->tooltip('View'), EditAction::make()->iconButton()->tooltip('Edit'), DeleteAction::make()->iconButton()->tooltip('Delete')]) // icons keep actions on screen at desktop width
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]); // soft delete; needs the module's delete permission
    }

    /** List-of-links JSON ([{platform, url}] or [url]) as plain URLs. */
    public static function links(mixed $links): array
    {
        return collect((array) $links)->map(fn ($l) => is_array($l) ? ($l['url'] ?? implode(' ', array_filter($l, 'is_string'))) : (string) $l)->filter()->values()->all();
    }
}
