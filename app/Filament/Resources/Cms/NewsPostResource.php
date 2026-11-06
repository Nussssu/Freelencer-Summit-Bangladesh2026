<?php

namespace App\Filament\Resources\Cms;

use App\Enums\NewsCategory;
use App\Filament\Fields\MediaPicker;
use App\Filament\Resources\Cms\Pages\ManageNewsPosts;
use App\Models\NewsPost;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/** News & updates: /news, each /news/{slug} article and the landing's latest news. A post is live from its publish date. */
class NewsPostResource extends CmsResource
{
    protected static string $module = 'news';

    protected static ?string $model = NewsPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'News';

    protected static ?string $pluralModelLabel = 'News posts';

    protected static ?string $modelLabel = 'news post';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 2;

    protected static function defaultSort(): array
    {
        return ['published_at', 'desc'];
    }

    protected static function columns(): array
    {
        return [
            ImageColumn::make('cover_image')->label('')->getStateUsing(fn (NewsPost $p) => $p->cover_image ? url($p->cover_image) : null)->square()->size(44),
            TextColumn::make('title')->searchable()->sortable()->weight('medium')->wrap(),
            TextColumn::make('category')->badge()->color('primary')->formatStateUsing(fn (NewsCategory $state) => $state->label()),
            TextColumn::make('published_at')->label('Published')->dateTime('d M Y, h:i A')->sortable()->placeholder('Draft')
                ->description(fn (NewsPost $p) => $p->published_at?->isFuture() ? 'Scheduled' : null),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            SelectFilter::make('category')->options(array_column(NewsCategory::options(), 'label', 'value')),
            TernaryFilter::make('published_at')->label('Published')->nullable(),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Post')->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->required()->maxLength(200)->columnSpanFull()->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(200)->alphaDash()->unique(NewsPost::class, 'slug', ignoreRecord: true)->helperText('Article page: /news/{slug}'),
                Select::make('category')->options(array_column(NewsCategory::options(), 'label', 'value'))->required()->native(false),
                DateTimePicker::make('published_at')->label('Publish at')->seconds(false)->timezone(config('summit.timezone'))->default(now())
                    ->helperText('Empty keeps the post as a draft; a future time schedules it.'),
                MediaPicker::make('cover_image')->label('Cover image'),
                Textarea::make('excerpt')->rows(2)->maxLength(300)->columnSpanFull(),
                RichEditor::make('body')->required()->columnSpanFull()
                    ->toolbarButtons(['bold', 'italic', 'link', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'undo', 'redo']),
            ]),
            Section::make('Search engines')->columns(2)->columnSpanFull()->collapsed()->schema([
                TextInput::make('seo_title')->label('SEO title')->maxLength(70),
                Textarea::make('seo_description')->label('SEO description')->rows(2)->maxLength(160),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsPosts::route('/')];
    }
}
