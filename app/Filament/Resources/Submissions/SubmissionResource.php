<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Concerns\ChecksModulePermission;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Read-only table of one public form's submissions, opened in a view modal. Encrypted columns (phones) are decrypted
 * by the model casts for display only; they are never searchable or exported. Each form's own resource sets the columns.
 */
abstract class SubmissionResource extends Resource
{
    use ChecksModulePermission { getAuthorizationResponse as modulePermission; }

    protected static string|UnitEnum|null $navigationGroup = 'Leads';

    /** Actions beyond viewing (e.g. award status) are added per resource and authorised as "edit". */
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        return in_array($action, ['view', 'viewAny', 'update'], true) ? static::modulePermission($action, $record) : Response::deny();
    }

    /** @return list<Column> */
    abstract protected static function columns(): array;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Received')->dateTime('j M Y, H:i')->sortable(),
                ...static::columns(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([ViewAction::make()]);
    }

    /** List-of-links JSON ([{platform, url}] or [url]) as plain URLs. */
    public static function links(mixed $links): array
    {
        return collect((array) $links)->map(fn ($l) => is_array($l) ? ($l['url'] ?? implode(' ', array_filter($l, 'is_string'))) : (string) $l)->filter()->values()->all();
    }
}
