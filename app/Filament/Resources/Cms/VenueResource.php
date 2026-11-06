<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Resources\Cms\Pages\ManageVenues;
use App\Models\Venue;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use UnitEnum;

/** Venues & halls: where agenda sessions take place (each session picks a hall; the hall name shows on the agenda). */
class VenueResource extends CmsResource
{
    protected static string $module = 'agenda';

    protected static ?string $model = Venue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static ?string $navigationLabel = 'Venues & halls';

    protected static ?string $pluralModelLabel = 'Venues & halls';

    protected static ?string $modelLabel = 'venue';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 7;

    protected static function defaultSort(): array
    {
        return ['name', 'asc'];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('city')->sortable(),
            TextColumn::make('address')->limit(50)->toggleable(),
            TextColumn::make('halls.name')->label('Halls')->badge()->color('gray'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Venue')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(160)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(160)->alphaDash()->unique(Venue::class, 'slug', ignoreRecord: true),
                TextInput::make('address')->maxLength(255)->columnSpanFull(),
                TextInput::make('city')->maxLength(80)->default('Dhaka'),
                TextInput::make('lat')->label('Latitude')->numeric(),
                TextInput::make('lng')->label('Longitude')->numeric(),
            ]),
            Section::make('Halls')->columnSpanFull()->schema([
                Repeater::make('halls')->hiddenLabel()->relationship('halls')->orderColumn('sort')->schema([
                    TextInput::make('name')->required()->maxLength(120)->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                    TextInput::make('slug')->required()->maxLength(120)->alphaDash(),
                ])->columns(2)->addActionLabel('Add hall')->defaultItems(0),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageVenues::route('/')];
    }
}
