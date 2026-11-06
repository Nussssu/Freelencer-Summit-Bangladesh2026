<?php

namespace App\Filament\Resources\Cms;

use App\Enums\SessionType;
use App\Filament\Resources\Cms\Pages\ManageAgendaSessions;
use App\Models\AgendaSession;
use App\Models\Hall;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Agenda & sessions: the /agenda programme (day tabs, type, hall, time and speaker filters), each session's own page
 * and calendar file, the landing's agenda preview and the Grand Summit's. Day 1 is the summit's start date
 * (Event Management › Grand Summit); sessions run in date, start time, then sort order.
 */
class AgendaSessionResource extends CmsResource
{
    protected static string $module = 'agenda';

    protected static ?string $model = AgendaSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static ?string $navigationLabel = 'Agenda & sessions';

    protected static ?string $pluralModelLabel = 'Agenda sessions';

    protected static ?string $modelLabel = 'session';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 5;

    protected static function defaultSort(): array
    {
        return ['date', 'asc'];
    }

    protected static function exportColumns(): array
    {
        return [
            'Title' => 'title', 'Type' => 'type', 'Date' => fn (AgendaSession $s) => $s->date?->format('Y-m-d'), 'Starts' => 'starts_at', 'Ends' => 'ends_at',
            'Hall' => 'hall.name', 'Track' => 'track', 'Speakers' => fn (AgendaSession $s) => $s->speakers->pluck('name')->whenEmpty(fn ($c) => $c->push($s->speaker_name))->filter()->implode('; '),
            'Moderator' => 'moderator.name', 'Visible' => 'visible',
        ];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('date')->date('D, d M')->sortable(),
            TextColumn::make('starts_at')->label('Time')->formatStateUsing(fn ($state, AgendaSession $s) => trim(substr((string) $s->starts_at, 0, 5).'–'.substr((string) $s->ends_at, 0, 5), '–'))->sortable(),
            TextColumn::make('title')->searchable()->sortable()->weight('medium')->wrap(),
            TextColumn::make('type')->badge()->color('primary')->formatStateUsing(fn (SessionType $state) => $state->label()),
            TextColumn::make('hall.name')->label('Hall')->sortable(),
            TextColumn::make('speakers.name')->label('Speakers')->badge()->color('gray')->placeholder(fn (AgendaSession $s) => $s->speaker_name ?: '—')->toggleable(),
            IconColumn::make('visible')->boolean(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn ($query) => $query->orderBy('starts_at')->orderBy('sort'))
            ->filters([
                SelectFilter::make('type')->options(array_column(SessionType::options(), 'label', 'value')),
                SelectFilter::make('hall')->relationship('hall', 'name'),
                TernaryFilter::make('visible'),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Session')->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->required()->maxLength(200)->columnSpanFull()->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(200)->alphaDash()->unique(AgendaSession::class, 'slug', ignoreRecord: true)->helperText('Session page: /agenda/session/{slug}'),
                Select::make('type')->options(array_column(SessionType::options(), 'label', 'value'))->required()->native(false),
                DatePicker::make('date')->required()->default(fn () => substr((string) config('summit.starts_at'), 0, 10))->helperText('Day 1 is the Grand Summit start date.'),
                Select::make('hall_id')->label('Hall')->options(fn () => Hall::with('venue')->orderBy('sort')->get()->mapWithKeys(fn (Hall $h) => [$h->id => $h->venue ? "{$h->name} · {$h->venue->name}" : $h->name]))
                    ->searchable()->helperText('Manage halls in Event Management › Venues & halls.'),
                TimePicker::make('starts_at')->label('Starts')->seconds(false)->required(),
                TimePicker::make('ends_at')->label('Ends')->seconds(false)->required()->after('starts_at'),
                TextInput::make('track')->maxLength(80)->placeholder('Industry & policy'),
                TextInput::make('sort')->numeric()->default(0)->helperText('Orders sessions that start at the same time.'),
                Textarea::make('summary')->rows(2)->maxLength(300)->columnSpanFull(),
                Textarea::make('description')->label('Full description')->rows(4)->columnSpanFull(),
                TagsInput::make('learn')->label('What you will learn')->placeholder('Add a point')->columnSpanFull(),
                TagsInput::make('audience')->label('Who should attend')->placeholder('Add an audience')->columnSpanFull(),
            ]),
            Section::make('Speakers')->columns(2)->columnSpanFull()->schema([
                Select::make('speakers')->relationship('speakers', 'name')->multiple()->searchable()->preload()->columnSpanFull()
                    ->helperText('People from Event Management › Speakers & experts; the first is the session\'s featured speaker.'),
                TextInput::make('speaker_name')->label('Speaker name (not in the speaker list)')->maxLength(160)->helperText('Used only when no speaker is linked above.'),
                Select::make('moderator_id')->label('Moderator')->relationship('moderator', 'name')->searchable()->preload(),
                Toggle::make('visible')->label('Show on the website')->default(true),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAgendaSessions::route('/')];
    }
}
