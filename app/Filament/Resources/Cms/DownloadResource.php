<?php

namespace App\Filament\Resources\Cms;

use App\Enums\DownloadKind;
use App\Filament\Resources\Cms\Pages\ManageDownloads;
use App\Models\Download;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

/**
 * Downloads: the brochure, agenda, sponsorship prospectus, award guidelines and media kit PDFs. Each kind has one
 * file; its download card on the site (agenda, sponsorship, awards, media pages) is enabled while it is visible.
 * Uploads are PDF only, size-limited, stored under random names on the public disk.
 */
class DownloadResource extends CmsResource
{
    protected static string $module = 'downloads';

    protected static ?string $model = Download::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Downloads';

    protected static ?string $pluralModelLabel = 'Downloads';

    protected static ?string $modelLabel = 'download';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 6;

    protected static function defaultSort(): array
    {
        return ['kind', 'asc'];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('kind')->badge()->color('primary')->formatStateUsing(fn (DownloadKind $state) => $state->label()),
            TextColumn::make('title')->searchable()->weight('medium'),
            TextColumn::make('file_path')->label('File')->formatStateUsing(fn (?string $state) => $state ? basename($state) : null)->placeholder('No file')
                ->url(fn (Download $d) => $d->file_path ? Storage::disk('public')->url($d->file_path) : null, true),
            TextColumn::make('size')->label('Size')->getStateUsing(fn (Download $d) => $d->file_path && Storage::disk('public')->exists($d->file_path) ? number_format(Storage::disk('public')->size($d->file_path) / 1048576, 1).' MB' : null)->placeholder('—'),
            IconColumn::make('visible')->boolean(),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                Select::make('kind')->options(array_column(DownloadKind::options(), 'label', 'value'))->required()->native(false)
                    ->unique(Download::class, 'kind', ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule),
                TextInput::make('title')->required()->maxLength(160),
                FileUpload::make('file_path')->label('PDF file')->disk('public')->directory('downloads')->visibility('public')
                    ->acceptedFileTypes(['application/pdf'])->maxSize(20480)->required()->downloadable()->columnSpanFull()
                    ->helperText('PDF, up to 20 MB.'),
                Toggle::make('visible')->default(true)->helperText('Off disables the download card on the site.'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDownloads::route('/')];
    }
}
