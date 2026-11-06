<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Fields\MediaPicker;
use App\Filament\Resources\Cms\Pages\ManageSponsors;
use App\Models\Sponsor;
use App\Models\SponsorPackage;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

/**
 * Sponsors by tier: the partner wall on the landing, /partners and /sponsors, and the tiers on /sponsorship-opportunity.
 * Only confirmed sponsors appear on the site; tiers (packages) are created inline and ordered by their sort number.
 */
class SponsorResource extends CmsResource
{
    protected static string $module = 'partners';

    protected static ?string $model = Sponsor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Sponsors';

    protected static ?string $pluralModelLabel = 'Sponsors';

    protected static ?string $modelLabel = 'sponsor';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 8;

    protected static function columns(): array
    {
        return [
            ImageColumn::make('logo')->label('')->getStateUsing(fn (Sponsor $s) => $s->logo ? url($s->logo) : null)->height(32),
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('package.name')->label('Tier')->badge()->color('primary')->sortable(),
            TextColumn::make('sort')->label('Order in tier')->sortable()->toggleable(),
            IconColumn::make('confirmed')->label('Confirmed')->boolean(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn ($query) => $query->with('package'))
            ->defaultSort(fn ($query) => $query->orderBy(SponsorPackage::select('sort')->whereColumn('sponsor_packages.id', 'sponsors.sponsor_package_id'))->orderBy('sort'))
            ->filters([
                SelectFilter::make('package')->label('Tier')->relationship('package', 'name'),
                TernaryFilter::make('confirmed'),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(160)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(160)->alphaDash()->unique(Sponsor::class, 'slug', ignoreRecord: true),
                Select::make('sponsor_package_id')->label('Tier')->relationship('package', 'name', fn ($query) => $query->orderBy('sort'))->required()->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(80)->live(onBlur: true)->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->required()->maxLength(80)->alphaDash()->unique(SponsorPackage::class, 'slug'),
                        TagsInput::make('benefits'),
                        TextInput::make('sort')->numeric()->default(fn () => (int) SponsorPackage::max('sort') + 1),
                        Toggle::make('provisional')->default(true)->helperText('Shows the "provisional" note until agreements are signed.'),
                    ]),
                TextInput::make('url')->label('Website')->url()->maxLength(255),
                MediaPicker::make('logo'),
                TextInput::make('sort')->label('Order in tier')->numeric()->default(0),
                Toggle::make('confirmed')->default(true)->helperText('Only confirmed sponsors appear on the site.'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSponsors::route('/')];
    }
}
