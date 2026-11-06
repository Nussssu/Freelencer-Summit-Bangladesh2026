<?php

namespace App\Filament\Resources\Cms;

use App\Enums\PartnerCategory;
use App\Filament\Fields\MediaPicker;
use App\Filament\Resources\Cms\Pages\ManagePartners;
use App\Models\Partner;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/** Partners: the landing's partner strip, grouped by category (government & development, strategic, supporting). */
class PartnerResource extends CmsResource
{
    protected static string $module = 'partners';

    protected static ?string $model = Partner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Partners';

    protected static ?string $pluralModelLabel = 'Partners';

    protected static ?string $modelLabel = 'partner';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 7;

    protected static function columns(): array
    {
        return [
            TextColumn::make('sort')->label('#')->sortable()->width('3rem'),
            ImageColumn::make('logo')->label('')->getStateUsing(fn (Partner $p) => $p->logo ? url($p->logo) : null)->height(32),
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('category')->badge()->color('primary')->formatStateUsing(fn (PartnerCategory $state) => $state->label()),
            TextColumn::make('url')->label('Website')->limit(32)->url(fn (Partner $p) => $p->url, true)->toggleable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->reorderable('sort')->filters([
            SelectFilter::make('category')->options(array_column(PartnerCategory::options(), 'label', 'value')),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(160)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(160)->alphaDash()->unique(Partner::class, 'slug', ignoreRecord: true),
                Select::make('category')->options(array_column(PartnerCategory::options(), 'label', 'value'))->required()->native(false),
                TextInput::make('url')->label('Website')->url()->maxLength(255),
                MediaPicker::make('logo'),
                TextInput::make('sort')->numeric()->default(fn () => (int) Partner::max('sort') + 1),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePartners::route('/')];
    }
}
