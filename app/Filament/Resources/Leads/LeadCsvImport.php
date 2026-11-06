<?php

namespace App\Filament\Resources\Leads;

use App\Models\AuditLog;
use App\Models\Lead;
use App\Support\Access;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

/**
 * Import CSV beside Export CSV: reads a file in the export's own columns (Received, Type, Reference, Name, Email, Phone,
 * Status, Follow up, Notes), so an exported file can be edited and imported back. Header names are matched loosely
 * (case and spacing). Rows without a name and email are skipped, and so are rows already in Leads (same reference, or
 * same email and type). Imported leads send no emails.
 */
class LeadCsvImport
{
    private const HEADERS = [
        'received' => 'created_at', 'created at' => 'created_at', 'date' => 'created_at',
        'type' => 'type', 'reference' => 'reference', 'name' => 'name', 'full name' => 'name',
        'email' => 'email', 'email address' => 'email', 'phone' => 'phone', 'mobile' => 'phone',
        'status' => 'status', 'follow up' => 'follow_up_at', 'notes' => 'notes',
    ];

    public static function action(): Action
    {
        return Action::make('import')
            ->label('Import CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->visible(fn () => (bool) auth()->user()?->can(Access::permission('edit', 'leads')))
            ->modalHeading('Import leads from CSV')
            ->modalDescription('Use the same columns as Export CSV: Received, Type, Reference, Name, Email, Phone, Status, Follow up, Notes. Only Name or Email is required. Leads already in the list are skipped, and imported leads send no emails.')
            ->modalSubmitActionLabel('Import')
            ->schema([
                FileUpload::make('file')->label('CSV file')->required()->storeFiles(false)->maxSize(5120)
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel']),
            ])
            ->action(function (array $data) {
                $file = $data['file'] instanceof TemporaryUploadedFile ? $data['file'] : null;
                if (! $file) {
                    Notification::make()->danger()->title('No file uploaded')->send();

                    return;
                }
                try {
                    [$created, $skipped] = self::import($file->getRealPath());
                } catch (Throwable $e) {
                    Notification::make()->danger()->title('Import failed')->body($e->getMessage())->persistent()->send();

                    return;
                }
                AuditLog::record('Imported', 'Leads', "Imported {$created} leads from CSV");
                Notification::make()->success()->title("Imported {$created} ".Str::plural('lead', $created))
                    ->body($skipped ? "{$skipped} ".Str::plural('row', $skipped).' skipped (empty or already in Leads).' : null)->send();
            });
    }

    /** @return array{0: int, 1: int} created, skipped */
    public static function import(string $path): array
    {
        $in = fopen($path, 'r');
        throw_unless($in, new \RuntimeException('The file could not be read.'));
        $header = fgetcsv($in, escape: '\\');
        throw_unless($header, new \RuntimeException('The file is empty.'));
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]); // Excel's UTF-8 BOM
        $map = array_map(fn ($h) => self::HEADERS[Str::of((string) $h)->lower()->squish()->value()] ?? null, $header);
        throw_unless(in_array('name', $map, true) || in_array('email', $map, true), new \RuntimeException('No "Name" or "Email" column found. Use the Export CSV columns as headers.'));

        $statuses = array_change_key_case(array_flip(Lead::STATUSES)) + array_combine(array_keys(Lead::STATUSES), array_keys(Lead::STATUSES));
        $created = $skipped = 0;
        while (($cells = fgetcsv($in, escape: '\\')) !== false) {
            $row = [];
            foreach ($map as $i => $key) {
                if ($key !== null && isset($cells[$i])) {
                    $row[$key] = self::clean($cells[$i]);
                }
            }
            $row = array_filter($row, fn ($v) => $v !== '');
            if (! isset($row['name']) && ! isset($row['email'])) {
                $skipped++;

                continue;
            }
            $type = Str::snake(Str::lower($row['type'] ?? 'imported'));
            $email = isset($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL) ? Str::lower($row['email']) : null;
            $exists = isset($row['reference'])
                ? Lead::where('reference', $row['reference'])->exists()
                : $email && Lead::where('email', $email)->where('type', $type)->exists();
            if ($exists) {
                $skipped++;

                continue;
            }

            $lead = new Lead([
                'type' => Str::limit($type, 60, ''),
                'reference' => isset($row['reference']) ? Str::limit($row['reference'], 40, '') : null,
                'name' => $row['name'] ?? null,
                'email' => $email,
                'phone' => $row['phone'] ?? null,
                'status' => $statuses[Str::lower($row['status'] ?? '')] ?? 'new',
                'notes' => $row['notes'] ?? null,
                'follow_up_at' => self::date($row['follow_up_at'] ?? null),
                'data' => array_intersect_key($row, array_flip(['name', 'email', 'phone', 'reference', 'notes'])) + ['source' => 'CSV import'],
            ]);
            if ($at = self::date($row['created_at'] ?? null)) {
                $lead->created_at = $at;
            }
            $lead->save();
            $created++;
        }
        fclose($in);

        return [$created, $skipped];
    }

    /** Trims the cell and undoes the export's formula guard (a leading ' before = + - @). */
    private static function clean(string $v): string
    {
        $v = trim($v);

        return preg_match("/^'[=+\\-@]/", $v) ? substr($v, 1) : $v;
    }

    private static function date(?string $v): ?Carbon
    {
        return $v ? rescue(fn () => Carbon::parse($v, config('summit.timezone')), null, report: false) : null;
    }
}
