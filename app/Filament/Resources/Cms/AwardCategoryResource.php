<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Resources\Cms\Pages\ManageAwardCategories;
use App\Models\AwardCategory;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Award categories: the /awards category cards, the nomination form's category step (only open categories accept
 * nominations) and each nomination's category. A category with nominations cannot be deleted, only closed.
 */
class AwardCategoryResource extends CmsResource
{
    protected static string $module = 'awards';

    protected static ?string $model = AwardCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Awards Management';

    protected static ?string $navigationLabel = 'Award categories';

    protected static ?string $pluralModelLabel = 'Award categories';

    protected static ?string $modelLabel = 'award category';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static function columns(): array
    {
        return [
            TextColumn::make('sort')->label('#')->sortable()->width('3rem'),
            TextColumn::make('name')->searchable()->sortable()->weight('medium')->description(fn (AwardCategory $c) => Str::limit((string) $c->description, 80)),
            TextColumn::make('deadline')->date('d M Y')->sortable()->placeholder('Not set'),
            IconColumn::make('is_open')->label('Open')->boolean(),
            TextColumn::make('applications_count')->counts('applications')->label('Nominations')->sortable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->reorderable('sort')->checkIfRecordIsSelectableUsing(fn (AwardCategory $c) => $c->applications()->doesntExist());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(120)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(120)->alphaDash()->unique(AwardCategory::class, 'slug', ignoreRecord: true)->disabledOn('edit')
                    ->helperText('Fixed once created: nominations refer to it.'),
                Textarea::make('description')->label('Who it is for')->rows(2)->maxLength(300)->columnSpanFull(),
                Textarea::make('eligibility')->rows(3)->columnSpanFull(),
                TagsInput::make('criteria')->label('Judging criteria')->columnSpanFull(),
                DatePicker::make('deadline')->label('Nomination deadline'),
                TextInput::make('sort')->numeric()->default(fn () => (int) AwardCategory::max('sort') + 1),
                Toggle::make('is_open')->label('Accepting nominations')->default(true),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAwardCategories::route('/')];
    }
}
