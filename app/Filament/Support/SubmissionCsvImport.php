<?php

namespace App\Filament\Support;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Rules\BdMobile;
use App\Support\Access;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

/**
 * Import CSV beside Export CSV on a submissions table. The file uses the export's own headers plus Phone (exports leave
 * phones out, but every registration needs one). Headers match loosely (case and spacing). Rows that are empty, already
 * there (same reference, or same email where the form allows one per person) or missing a required value are skipped
 * and counted. Imported rows get a reference like a form submission, appear in Leads, and send no emails.
 */
class SubmissionCsvImport
{
    /** @param class-string<SubmissionResource> $resource */
    public static function action(string $resource): Action
    {
        $columns = array_keys(self::columns($resource));

        return Action::make('import')
            ->label('Import CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->visible(fn () => $resource::can('update'))
            ->modalHeading('Import '.Str::lower($resource::getPluralModelLabel()).' from CSV')
            ->modalDescription('First row = column names: '.implode(', ', $columns).'. Required: '.implode(', ', self::requiredLabels($resource)).'. Rows already in the list are skipped, and imported rows send no emails.')
            ->modalSubmitActionLabel('Import')
            ->schema([
                FileUpload::make('file')->label('CSV file')->required()->storeFiles(false)->maxSize(5120)
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel']),
            ])
            ->action(function (array $data) use ($resource) {
                $file = $data['file'] ?? null;
                if (! $file instanceof TemporaryUploadedFile) {
                    Notification::make()->danger()->title('No file uploaded')->send();

                    return;
                }
                try {
                    [$created, $skipped] = self::import($resource, $file->getRealPath());
                } catch (Throwable $e) {
                    Notification::make()->danger()->title('Import failed')->body($e->getMessage())->persistent()->send();

                    return;
                }
                AuditLog::record('Imported', $resource::getPluralModelLabel(), "Imported {$created} rows from CSV");
                Notification::make()->success()->title("Imported {$created} ".Str::plural('row', $created))
                    ->body($skipped ? "{$skipped} ".Str::plural('row', $skipped).' skipped (empty, already in the list, or missing '.implode(', ', self::requiredLabels($resource)).').' : null)->send();
            });
    }

    /**
     * @param class-string<SubmissionResource> $resource
     *
     * @return array{0: int, 1: int} created, skipped
     */
    public static function import(string $resource, string $path): array
    {
        $columns = self::columns($resource);
        $byHeader = [];
        foreach ($columns as $label => $target) {
            $byHeader[self::key($label)] = $target;
        }

        $in = fopen($path, 'r');
        throw_unless($in, new \RuntimeException('The file could not be read.'));
        $header = fgetcsv($in, escape: '\\');
        throw_unless($header, new \RuntimeException('The file is empty.'));
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]); // Excel's UTF-8 BOM
        $map = array_map(fn ($h) => $byHeader[self::key((string) $h)] ?? null, $header);
        $required = $resource::importRequired();
        $emailKey = collect($required)->first(fn ($k) => str_ends_with($k, 'email')) ?? 'email';

        $model = $resource::getModel();
        $created = $skipped = 0;
        Lead::$quiet = true; // imported rows appear in Leads but send no emails
        try {
            while (($cells = fgetcsv($in, escape: '\\')) !== false) {
                $attrs = [];
                foreach ($map as $i => $target) {
                    $cell = self::clean((string) ($cells[$i] ?? ''));
                    if ($target === null || $cell === '') {
                        continue;
                    }
                    $attrs = $target instanceof Closure ? [...$attrs, ...array_filter($target($cell), fn ($v) => $v !== null)] : [...$attrs, $target => $cell];
                }
                $attrs = [...$resource::importDefaults(), ...$attrs];
                if (collect($required)->contains(fn ($k) => blank($attrs[$k] ?? null)) || ! filter_var($attrs[$emailKey] ?? null, FILTER_VALIDATE_EMAIL)
                    || (isset($attrs['reference']) && $model::withTrashed()->where('reference', $attrs['reference'])->exists())) {
                    $skipped++;

                    continue;
                }
                $attrs[$emailKey] = Str::lower($attrs[$emailKey]);
                if ($resource::importDuplicate($attrs)) {
                    $skipped++;

                    continue;
                }
                $at = $attrs['created_at'] ?? null;
                $row = new $model([
                    ...array_diff_key($attrs, ['created_at' => 0]),
                    'consent_version' => 'import',
                    'consented_at' => $at ?? now(),
                ]);
                if ($at) {
                    $row->created_at = $at;
                }
                try {
                    $row->save();
                    $created++;
                } catch (Throwable) {
                    $skipped++; // duplicate email or a required value the file did not have
                }
            }
        } finally {
            Lead::$quiet = false;
            fclose($in);
        }

        return [$created, $skipped];
    }

    /** @return array<string, string|Closure> header label => attribute or fn (string): array */
    private static function columns(string $resource): array
    {
        $statuses = (fn () => static::statuses())->bindTo(null, $resource)() ?? [];

        return [
            'Reference' => 'reference',
            'Received' => fn (string $v) => ['created_at' => self::date($v)],
            ...$resource::importIdentity(),
            ...$resource::importColumns(),
            ...($statuses ? [
                'Status' => fn (string $v) => ['status' => self::pick($statuses, $v)],
                'Notes' => 'notes',
            ] : []),
        ];
    }

    /** Header names of the required columns, for the import window and the result message. */
    private static function requiredLabels(string $resource): array
    {
        $labels = [];
        foreach (self::columns($resource) as $label => $target) {
            $keys = is_string($target) ? [$target] : array_keys((array) rescue(fn () => $target('x'), [], report: false));
            if (array_intersect($keys, $resource::importRequired())) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /** The key whose key or label matches the cell (case-insensitive), or null. */
    public static function pick(array $options, string $value): ?string
    {
        $v = Str::lower(trim($value));
        foreach ($options as $key => $label) {
            if (Str::lower((string) $key) === $v || Str::lower((string) $label) === $v) {
                return (string) $key;
            }
        }

        return null;
    }

    /** "a; b" (the export's list format) → keys picked from the options, unknown values dropped. */
    public static function pickMany(array $options, string $value): array
    {
        return array_values(array_filter(array_map(fn ($p) => self::pick($options, $p), preg_split('/\s*[;,]\s*/', $value))));
    }

    /** Yes / No / true / 1 → bool, anything else → null. */
    public static function yes(string $value): ?bool
    {
        return match (Str::lower(trim($value))) {
            'yes', 'y', 'true', '1' => true,
            'no', 'n', 'false', '0' => false,
            default => null,
        };
    }

    private static function key(string $header): string
    {
        return Str::of($header)->lower()->squish()->value();
    }

    /** Trims and undoes the export's formula guard (a leading ' before = + - @). */
    private static function clean(string $v): string
    {
        $v = trim($v);

        return preg_match("/^'[=+\\-@]/", $v) ? substr($v, 1) : $v;
    }

    private static function date(string $v): ?Carbon
    {
        return rescue(fn () => Carbon::parse($v, config('summit.timezone')), null, report: false);
    }
}
