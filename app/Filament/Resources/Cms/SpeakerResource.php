<?php

namespace App\Filament\Resources\Cms;

use App\Filament\Fields\MediaPicker;
use App\Filament\Resources\Cms\Pages\ManageSpeakers;
use App\Models\Speaker;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

/**
 * Speakers & experts: the one list behind the landing's "The people on stage", the Speakers & Experts directory and
 * popups, each speaker's profile page, the Grand Summit's featured speakers, agenda sessions and programme pages.
 * Order is the "Sort" number (lowest first); hidden speakers disappear from the site everywhere.
 */
class SpeakerResource extends CmsResource
{
    protected static string $module = 'speakers';

    protected static ?string $model = Speaker::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static ?string $navigationLabel = 'Speakers & experts';

    protected static ?string $pluralModelLabel = 'Speakers & experts';

    protected static ?string $modelLabel = 'speaker';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 6;

    /** Roles in the order the site groups them; free text is allowed for others. */
    public const ROLES = ['Chief Guest' => 'Chief Guest', 'Special Guest' => 'Special Guest', 'Guest' => 'Guest', 'Speaker' => 'Speaker', 'Mentor' => 'Mentor', 'Moderator' => 'Moderator'];

    protected static function exportColumns(): array
    {
        return ['Name' => 'name', 'Role' => 'role_label', 'Designation' => 'designation', 'Organisation' => 'organisation', 'Country' => 'country', 'Expertise' => 'expertise', 'Featured' => 'featured', 'Visible' => 'visible', 'Sort' => 'sort'];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('sort')->label('#')->sortable()->width('3rem'),
            ImageColumn::make('photo')->label('')->getStateUsing(fn (Speaker $s) => $s->photo ? url($s->photo) : null)->circular()->size(40),
            TextColumn::make('name')->searchable()->sortable()->weight('medium')->description(fn (Speaker $s) => collect([$s->designation, $s->organisation])->filter()->implode(', ')),
            TextColumn::make('role_label')->label('Role')->badge()->color('primary')->sortable(),
            TextColumn::make('sessions_count')->counts('sessions')->label('Sessions')->toggleable(),
            IconColumn::make('featured')->boolean()->toggleable(),
            IconColumn::make('visible')->boolean(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->reorderable('sort')
            ->filters([
                SelectFilter::make('role_label')->label('Role')->options(fn () => Speaker::query()->distinct()->orderBy('role_label')->pluck('role_label', 'role_label')->filter()->all()),
                TernaryFilter::make('visible'),
                TernaryFilter::make('featured'),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Speaker')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(160)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(160)->alphaDash()->unique(Speaker::class, 'slug', ignoreRecord: true)->helperText('Profile page: /speaker/{slug}'),
                Select::make('role_label')->label('Role at the summit')->options(self::ROLES)->required()->native(false)->default('Speaker'),
                TextInput::make('country')->label('Country code')->required()->length(2)->default('BD')->alpha()
                    ->dehydrateStateUsing(fn (?string $state) => strtoupper((string) $state))->helperText('Two letters: BD, IN, GB… The site shows the country name.'),
                TextInput::make('designation')->maxLength(160),
                TextInput::make('organisation')->maxLength(160),
                MediaPicker::make('photo')->label('Portrait')->columnSpanFull(),
                Textarea::make('bio')->label('Biography')->rows(5)->columnSpanFull(),
                TagsInput::make('expertise')->placeholder('Add a topic')->columnSpanFull(),
                Repeater::make('links')->label('Links')->schema([
                    TextInput::make('label')->placeholder('LinkedIn')->maxLength(40),
                    TextInput::make('url')->url()->required()->maxLength(255),
                ])->columns(2)->defaultItems(0)->columnSpanFull(),
            ]),
            Section::make('On the website')->columns(3)->columnSpanFull()->schema([
                Toggle::make('visible')->default(true)->helperText('Off hides the speaker everywhere on the site.'),
                Toggle::make('featured')->helperText('Highlighted first in the directory.'),
                TextInput::make('sort')->numeric()->default(fn () => (int) Speaker::max('sort') + 1)->helperText('Lower comes first. Drag rows in the table to reorder.'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSpeakers::route('/')];
    }
}
