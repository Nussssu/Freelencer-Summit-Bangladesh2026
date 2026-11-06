<?php

namespace App\Filament\Resources\Cms;

use App\Enums\BoothStatus;
use App\Filament\Resources\Cms\Pages\ManageBooths;
use App\Models\Booth;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Exhibition booths: the floor plan on /exhibition (position and size in plan units) and each booth's status, which
 * drives the plan's colours and the "booths still available" counter. Assign an exhibitor in Content › Exhibitors.
 */
class BoothResource extends CmsResource
{
    protected static string $module = 'exhibitors';

    protected static ?string $model = Booth::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static ?string $navigationLabel = 'Exhibition booths';

    protected static ?string $pluralModelLabel = 'Exhibition booths';

    protected static ?string $modelLabel = 'booth';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?int $navigationSort = 8;

    private const COLORS = ['available' => 'success', 'reserved' => 'warning', 'confirmed' => 'info'];

    protected static function defaultSort(): array
    {
        return ['code', 'asc'];
    }

    protected static function exportColumns(): array
    {
        return ['Booth' => 'code', 'Status' => 'status', 'Size' => 'size', 'Exhibitor' => 'exhibitor.name'];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('code')->label('Booth')->searchable()->sortable()->weight('medium'),
            TextColumn::make('status')->badge()->formatStateUsing(fn (BoothStatus $state) => $state->label())->color(fn (BoothStatus $state) => self::COLORS[$state->value] ?? 'gray'),
            TextColumn::make('size')->placeholder('—'),
            TextColumn::make('exhibitor.name')->label('Exhibitor')->placeholder('Unassigned'),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            SelectFilter::make('status')->options(array_column(BoothStatus::options(), 'label', 'value')),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('code')->label('Booth code')->required()->maxLength(16)->unique(Booth::class, 'code', ignoreRecord: true),
                Select::make('status')->options(array_column(BoothStatus::options(), 'label', 'value'))->required()->native(false)->default('available'),
                TextInput::make('size')->maxLength(32)->placeholder('3m × 3m'),
            ]),
            Section::make('Position on the floor plan')->columns(4)->columnSpanFull()->collapsed()->schema([
                TextInput::make('x')->numeric()->required()->default(0),
                TextInput::make('y')->numeric()->required()->default(0),
                TextInput::make('w')->label('Width')->numeric()->required()->default(90),
                TextInput::make('h')->label('Height')->numeric()->required()->default(90),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBooths::route('/')];
    }
}
