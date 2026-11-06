<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Fields\MediaPicker;
use App\Filament\Resources\Cms\Pages\ManageSuccessStories;
use App\Models\SuccessStory;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Success stories: the /success-stories wall and the landing's "Real people, real impact" cards (the first three
 * published, in sort order; a card with a YouTube video opens it in a new tab).
 */
class SuccessStoryResource extends CmsResource
{
    protected static string $module = 'stories';

    protected static ?string $model = SuccessStory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Success stories';

    protected static ?string $pluralModelLabel = 'Success stories';

    protected static ?string $modelLabel = 'success story';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    /** Programme the story came from (the /success-stories filter). */
    public const KINDS = ['Freelancer' => 'Freelancer', 'Entrepreneur' => 'Entrepreneur', 'Student' => 'Student'];

    protected static function columns(): array
    {
        return [
            TextColumn::make('sort')->label('#')->sortable()->width('3rem'),
            ImageColumn::make('photo')->label('')->getStateUsing(fn (SuccessStory $s) => $s->photo ? url($s->photo) : null)->square()->size(44),
            TextColumn::make('name')->searchable()->sortable()->weight('medium')->description(fn (SuccessStory $s) => $s->role),
            TextColumn::make('headline')->searchable()->wrap()->limit(60),
            TextColumn::make('kind')->badge()->color('primary')->formatStateUsing(fn (?string $state) => self::KINDS[$state] ?? Str::headline((string) $state)),
            IconColumn::make('youtube_id')->label('Video')->boolean()->getStateUsing(fn (SuccessStory $s) => filled($s->youtube_id)),
            IconColumn::make('published')->boolean(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->reorderable('sort')->filters([TernaryFilter::make('published')]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Story')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(120)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(160)->alphaDash()->unique(SuccessStory::class, 'slug', ignoreRecord: true),
                TextInput::make('role')->label('Role / profession')->maxLength(120)->placeholder('UI/UX Designer'),
                Select::make('kind')->label('Story type')->options(self::KINDS)->native(false)->required()->default('Freelancer')->helperText('Filters on /success-stories.'),
                TextInput::make('headline')->label('Title')->required()->maxLength(160)->columnSpanFull(),
                Textarea::make('body')->label('Quote / story')->rows(4)->columnSpanFull(),
                MediaPicker::make('photo'),
                TextInput::make('youtube_id')->label('YouTube video')->maxLength(255)->placeholder('https://www.youtube.com/watch?v=…')
                    ->helperText('Paste the video link; the card opens it on YouTube.')
                    ->dehydrateStateUsing(fn (?string $state) => SuccessStory::youtubeId($state))
                    ->formatStateUsing(fn (?string $state) => $state ? "https://www.youtube.com/watch?v={$state}" : null),
            ]),
            Section::make('On the website')->columns(2)->columnSpanFull()->schema([
                Toggle::make('published')->default(true),
                TextInput::make('sort')->numeric()->default(fn () => (int) SuccessStory::max('sort') + 1)->helperText('The first three published show on the landing page.'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSuccessStories::route('/')];
    }
}
