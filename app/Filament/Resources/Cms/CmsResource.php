<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Concerns\ChecksModulePermission;
use App\Filament\Support\CsvExport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum;

/**
 * A content or event module the admin owns end to end (create, edit, delete in modals) and the public site reads
 * directly: speakers, sessions, programmes, news, stories, partners… Permissions come from the module ("{verb} {module}").
 * Each resource sets its columns (static::columns()), form, and optionally CSV columns and default sort.
 */
abstract class CmsResource extends Resource
{
    use ChecksModulePermission;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    /** @return list<\Filament\Tables\Columns\Column> */
    abstract protected static function columns(): array;

    /** CSV columns (label => attribute or fn (Model) => value); empty = no export. */
    protected static function exportColumns(): array
    {
        return [];
    }

    /** [column, direction] */
    protected static function defaultSort(): array
    {
        return ['sort', 'asc'];
    }

    public static function table(Table $table): Table
    {
        $export = static::exportColumns();

        return $table
            ->columns(static::columns())
            ->defaultSort(...static::defaultSort())
            ->headerActions([
                ...($export ? [CsvExport::action(static::getPluralModelLabel(), $export)] : []),
            ])
            ->recordActions([EditAction::make()->iconButton()->tooltip('Edit'), DeleteAction::make()->iconButton()->tooltip('Delete')])
            ->toolbarActions([BulkActionGroup::make([
                ...($export ? [CsvExport::bulk(static::getPluralModelLabel(), $export)] : []),
                DeleteBulkAction::make(),
            ])]);
    }

    /** The "New …" button on the list page. */
    public static function createAction(): CreateAction
    {
        return CreateAction::make()->mutateDataUsing(fn (array $data) => static::mutateCreateData($data));
    }

    /** Values a new row gets that the form does not ask for. */
    public static function mutateCreateData(array $data): array
    {
        return $data;
    }
}
