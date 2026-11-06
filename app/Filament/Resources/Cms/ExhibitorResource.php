<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Fields\MediaPicker;
use App\Filament\Resources\Cms\Pages\ManageExhibitors;
use App\Models\Booth;
use App\Models\Exhibitor;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/** Exhibitor profiles: /exhibition/exhibitors and each exhibitor's page (logo, sector, booth, description, website). */
class ExhibitorResource extends CmsResource
{
    protected static string $module = 'exhibitors';

    protected static ?string $model = Exhibitor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Exhibitor profiles';

    protected static ?string $pluralModelLabel = 'Exhibitor profiles';

    protected static ?string $modelLabel = 'exhibitor';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 9;

    protected static function defaultSort(): array
    {
        return ['name', 'asc'];
    }

    protected static function exportColumns(): array
    {
        return ['Name' => 'name', 'Sector' => 'sector', 'Booth' => 'booth.code', 'Website' => 'website', 'Visible' => 'visible'];
    }

    protected static function columns(): array
    {
        return [
            ImageColumn::make('logo')->label('')->getStateUsing(fn (Exhibitor $e) => $e->logo ? url($e->logo) : null)->square()->size(40),
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('sector')->badge()->color('primary')->sortable(),
            TextColumn::make('booth.code')->label('Booth')->sortable()->placeholder('—'),
            IconColumn::make('visible')->boolean(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            SelectFilter::make('sector')->options(fn () => Exhibitor::query()->distinct()->orderBy('sector')->pluck('sector', 'sector')->filter()->all()),
            TernaryFilter::make('visible'),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(160)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(160)->alphaDash()->unique(Exhibitor::class, 'slug', ignoreRecord: true)->helperText('Profile page: /exhibition/exhibitors/{slug}'),
                TextInput::make('sector')->required()->maxLength(80)->datalist(fn () => Exhibitor::query()->distinct()->orderBy('sector')->pluck('sector')->filter()->all()),
                Select::make('booth_id')->label('Booth')->options(fn (?Exhibitor $record) => Booth::query()
                    ->where(fn ($query) => $query->whereDoesntHave('exhibitor')->when($record?->booth_id, fn ($query, $id) => $query->orWhere('id', $id)))
                    ->orderBy('code')->pluck('code', 'id'))->searchable()->helperText('Free booths only; manage booths in Event Management › Exhibition booths.'),
                TextInput::make('website')->url()->maxLength(255),
                MediaPicker::make('logo'),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                Toggle::make('visible')->default(true),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageExhibitors::route('/')];
    }
}
