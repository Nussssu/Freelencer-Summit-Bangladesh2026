<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Support\CsvExport;
use App\Support\Statuses;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * One public form's submissions: view, correct or delete (soft delete) in modals; never created from the admin.
 * Each submission moves through its workflow statuses (static::statuses()) with internal notes, and the table exports
 * to CSV as filtered or as selected. Encrypted columns (phones) are decrypted by the model casts for display and
 * editing only; they are never searchable or exported. Each form's own resource sets the columns, view and edit form.
 */
abstract class SubmissionResource extends Resource
{
    use ChecksModulePermission { getAuthorizationResponse as modulePermission; }

    protected static string|UnitEnum|null $navigationGroup = 'Leads & CRM';

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

    /** Workflow statuses (value => label), or null for a form without one. */
    protected static function statuses(): ?array
    {
        return null;
    }

    /** CSV columns (label => attribute or fn (Model) => value); phones are deliberately left out. */
    protected static function exportColumns(): array
    {
        return ['Reference' => 'reference', 'Received' => 'created_at'];
    }

    /** Submissions with a status still to act on, for the sidebar badge. */
    public static function getNavigationBadge(): ?string
    {
        $first = array_key_first(static::statuses() ?? []);
        if (! $first) {
            return null;
        }
        $n = static::getModel()::query()->where('status', $first)->count();

        return $n ? (string) $n : null;
    }

    public static function table(Table $table): Table
    {
        $statuses = static::statuses();
        $export = [...static::exportColumns(), ...($statuses ? ['Status' => fn (Model $m) => $statuses[self::statusValue($m)] ?? self::statusValue($m), 'Notes' => 'notes'] : [])];

        return $table
            ->columns([
                TextColumn::make('created_at')->label('Received')->dateTime('d M Y, h:i A')->sortable(),
                ...static::columns(),
                ...($statuses ? [static::statusColumn()] : []),
            ])
            ->filters($statuses ? [static::statusFilter()] : [])
            ->defaultSort('created_at', 'desc')
            ->headerActions([CsvExport::action(static::getPluralModelLabel(), $export)])
            ->recordActions([ViewAction::make()->iconButton()->tooltip('View'), EditAction::make()->iconButton()->tooltip('Edit'), DeleteAction::make()->iconButton()->tooltip('Delete')]) // icons keep actions on screen at desktop width
            ->toolbarActions([BulkActionGroup::make([
                ...($statuses ? [static::setStatusBulk($statuses)] : []),
                CsvExport::bulk(static::getPluralModelLabel(), $export),
                DeleteBulkAction::make(), // soft delete; needs the module's delete permission
            ])]);
    }

    protected static function statusColumn(): TextColumn
    {
        return TextColumn::make('status')->badge()->sortable()
            ->formatStateUsing(fn ($state) => static::statuses()[self::statusValue($state)] ?? self::statusValue($state))
            ->color(fn ($state) => Statuses::color(self::statusValue($state)));
    }

    protected static function statusFilter(): SelectFilter
    {
        return SelectFilter::make('status')->options(static::statuses() ?? []);
    }

    protected static function setStatusBulk(array $statuses): BulkAction
    {
        return BulkAction::make('setStatus')->label('Set status')->icon('heroicon-o-tag')
            ->visible(fn () => static::can('update'))
            ->schema([Select::make('status')->options($statuses)->required()->native(false)])
            ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
            ->deselectRecordsAfterCompletion();
    }

    /** Status and internal notes, for the edit form. */
    protected static function workflowSection(): Section
    {
        return Section::make('Workflow')->columns(2)->columnSpanFull()->schema([
            Select::make('status')->options(static::statuses() ?? [])->required()->native(false),
            Textarea::make('notes')->label('Internal notes')->rows(3)->helperText('Only the team sees these. Never emailed.'),
        ]);
    }

    /** Status and notes, for the view modal. */
    protected static function workflowEntries(): Section
    {
        return Section::make('Workflow')->columns(2)->columnSpanFull()->schema([
            TextEntry::make('status')->badge()->formatStateUsing(fn ($state) => static::statuses()[self::statusValue($state)] ?? self::statusValue($state))->color(fn ($state) => Statuses::color(self::statusValue($state))),
            TextEntry::make('notes')->label('Internal notes')->placeholder('—'),
        ]);
    }

    private static function statusValue(mixed $v): ?string
    {
        $v = $v instanceof Model ? $v->getAttribute('status') : $v;

        return $v instanceof BackedEnum ? (string) $v->value : $v;
    }

    /** List-of-links JSON ([{platform, url}] or [url]) as plain URLs. */
    public static function links(mixed $links): array
    {
        return collect((array) $links)->map(fn ($l) => is_array($l) ? ($l['url'] ?? implode(' ', array_filter($l, 'is_string'))) : (string) $l)->filter()->values()->all();
    }
}
