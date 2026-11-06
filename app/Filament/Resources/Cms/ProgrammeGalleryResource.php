<?php

namespace App\Filament\Resources\Cms;

use App\Enums\MediaKind;
use App\Filament\Fields\MediaPicker;
use App\Filament\Resources\Cms\Pages\ManageProgrammeGallery;
use App\Models\MediaItem;
use App\Models\SuccessStory;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Programme photos and videos: the /media gallery (filtered by programme, division, district and campus) and each
 * programme page's "Photos and videos". Each item belongs to a programme, which gives it its place in the filters.
 */
class ProgrammeGalleryResource extends CmsResource
{
    protected static string $module = 'media';

    protected static ?string $model = MediaItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Programme gallery';

    protected static ?string $pluralModelLabel = 'Programme gallery';

    protected static ?string $modelLabel = 'gallery item';

    protected static ?string $slug = 'programme-gallery';

    protected static ?int $navigationSort = 5;

    protected static function columns(): array
    {
        return [
            ImageColumn::make('src')->label('')->getStateUsing(fn (MediaItem $m) => $m->src ? url($m->src) : ($m->youtube_id ? "https://i.ytimg.com/vi/{$m->youtube_id}/mqdefault.jpg" : null))->height(44),
            TextColumn::make('caption')->searchable()->wrap()->placeholder('—'),
            TextColumn::make('kind')->badge()->formatStateUsing(fn (MediaKind $state) => $state->label()),
            TextColumn::make('program.title')->label('Programme')->sortable()->wrap(),
            TextColumn::make('sort')->sortable()->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            SelectFilter::make('kind')->options(array_column(MediaKind::options(), 'label', 'value')),
            SelectFilter::make('program')->label('Programme')->relationship('program', 'title')->searchable()->preload(),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                Select::make('journey_program_id')->label('Programme')->relationship('program', 'title')->searchable()->preload()->required()->columnSpanFull(),
                Select::make('kind')->options(array_column(MediaKind::options(), 'label', 'value'))->required()->native(false)->default('photo')->live(),
                TextInput::make('sort')->numeric()->default(0),
                MediaPicker::make('src')->label('Photo')->visible(fn (Get $get) => $get('kind') !== MediaKind::Video->value)->required(fn (Get $get) => $get('kind') !== MediaKind::Video->value)->columnSpanFull(),
                TextInput::make('youtube_id')->label('YouTube video')->placeholder('https://www.youtube.com/watch?v=…')->visible(fn (Get $get) => $get('kind') === MediaKind::Video->value)
                    ->required(fn (Get $get) => $get('kind') === MediaKind::Video->value)->dehydrateStateUsing(fn (?string $state) => SuccessStory::youtubeId($state))->columnSpanFull(),
                TextInput::make('caption')->maxLength(200)->columnSpanFull(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageProgrammeGallery::route('/')];
    }
}
