<?php

namespace App\Filament\Support;

use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * CSV exports that open in Excel (UTF-8 BOM, so Bangla names survive): the table as currently filtered, or the selected
 * rows. Columns are label => attribute (dot notation for relations) or label => fn (Model) => value. Exports need the
 * module's view permission, like the table itself.
 */
class CsvExport
{
    /** @param array<string, string|Closure> $columns */
    public static function action(string $name, array $columns): Action
    {
        return Action::make('export')
            ->label('Export CSV')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(fn ($livewire) => self::stream($name, $columns, $livewire->getFilteredSortedTableQuery()));
    }

    /** @param array<string, string|Closure> $columns */
    public static function bulk(string $name, array $columns): BulkAction
    {
        return BulkAction::make('exportSelected')
            ->label('Export selected (CSV)')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(fn (Collection $records) => self::stream($name, $columns, $records))
            ->deselectRecordsAfterCompletion();
    }

    /** @param array<string, string|Closure> $columns  @param Builder|Collection $rows */
    public static function stream(string $name, array $columns, Builder|Collection $rows): StreamedResponse
    {
        $file = Str::slug($name).'-'.now(config('summit.timezone'))->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_keys($columns));
            $write = fn (Model $m) => fputcsv($out, array_map(fn ($c) => self::cell($c instanceof Closure ? $c($m) : data_get($m, $c)), $columns));
            $rows instanceof Builder ? $rows->lazy(500)->each($write) : $rows->each($write);
            fclose($out);
        }, $file, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private static function cell(mixed $v): string
    {
        $v = match (true) {
            $v instanceof BackedEnum && method_exists($v, 'label') => $v->label(),
            $v instanceof BackedEnum => (string) $v->value,
            $v instanceof UnitEnum => $v->name,
            $v instanceof \DateTimeInterface => $v->format('Y-m-d H:i'),
            is_bool($v) => $v ? 'Yes' : 'No',
            is_array($v) => implode('; ', array_map(fn ($x) => is_array($x) ? implode(' ', array_filter($x, 'is_scalar')) : (string) $x, $v)),
            default => (string) ($v ?? ''),
        };

        // Spreadsheet formula injection: a cell starting with = + - @ is read as a formula by Excel.
        return preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v;
    }
}
