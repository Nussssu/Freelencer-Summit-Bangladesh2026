<?php

namespace App\Filament\Resources\Cms;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Filament\Fields\MediaPicker;
use App\Models\JourneyProgram;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * One programme type's events (divisional summits, district roadshows or campus programmes): the rows behind the
 * directories, the national journey map, each programme's own page, its registration form and its gallery.
 * Each subclass fixes the type; speakers are linked here and appear on the programme page with their popup.
 */
abstract class ProgramResource extends CmsResource
{
    protected static string $module = 'programmes';

    protected static ?string $model = JourneyProgram::class;

    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static ?string $recordTitleAttribute = 'title';

    abstract public static function type(): ProgramType;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', static::type()->value);
    }

    protected static function defaultSort(): array
    {
        return ['starts_at', 'asc'];
    }

    protected static function exportColumns(): array
    {
        return [
            'Title' => 'title', 'Slug' => 'slug', 'Division' => 'division.name', 'District' => 'district.name', 'Campus' => 'campus.name',
            'Starts' => 'starts_at', 'Ends' => 'ends_at', 'Venue' => 'location', 'Partner' => 'partner', 'Status' => 'status',
            'Registration open' => 'registration_open', 'Participants' => 'participants', 'Registrations' => fn (JourneyProgram $p) => $p->registrations()->count(),
        ];
    }

    protected static function columns(): array
    {
        return [
            ImageColumn::make('cover_image')->label('')->getStateUsing(fn (JourneyProgram $p) => $p->cover_image ? url($p->cover_image) : null)->square()->size(44),
            TextColumn::make('title')->searchable()->sortable()->weight('medium')->wrap(),
            TextColumn::make('division.name')->label('Division')->sortable()->toggleable(),
            TextColumn::make('district.name')->label('District')->sortable()->toggleable(),
            TextColumn::make('campus.name')->label('Campus')->toggleable(isToggledHiddenByDefault: static::type() !== ProgramType::CampusProgram),
            TextColumn::make('starts_at')->label('Date')->date('d M Y')->sortable()->placeholder('To be confirmed'),
            TextColumn::make('status')->badge()->formatStateUsing(fn (ProgramStatus $state) => $state->label())
                ->color(fn (ProgramStatus $state) => match ($state) { ProgramStatus::RegistrationOpen => 'success', ProgramStatus::Completed => 'gray', ProgramStatus::Postponed => 'danger', default => 'warning' }),
            IconColumn::make('registration_open')->label('Reg. open')->boolean(),
            TextColumn::make('registrations_count')->counts('registrations')->label('Registrations')->sortable(),
            TextColumn::make('speakers_count')->counts('speakers')->label('Speakers')->toggleable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            SelectFilter::make('status')->options(array_column(ProgramStatus::options(), 'label', 'value')),
            SelectFilter::make('division')->relationship('division', 'name')->preload(),
            SelectFilter::make('district')->relationship('district', 'name')->searchable()->preload(),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        $campus = static::type() === ProgramType::CampusProgram;

        return $schema->components([
            Section::make('Programme')->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->required()->maxLength(160)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->maxLength(160)->alphaDash()
                    ->unique(JourneyProgram::class, 'slug', ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('type', static::type()->value))
                    ->helperText('The page address: /'.static::type()->pathSegment().'/{slug}'),
                Select::make('division_id')->label('Division')->relationship('division', 'name')->preload()->required(),
                Select::make('district_id')->label('District')->relationship('district', 'name')->searchable()->preload()->required(! $campus),
                ...($campus ? [Select::make('campus_id')->label('Campus')->relationship('campus', 'name')->searchable()->preload()->required()] : []),
                DateTimePicker::make('starts_at')->label('Starts')->seconds(false)->timezone(config('summit.timezone')),
                DateTimePicker::make('ends_at')->label('Ends')->seconds(false)->timezone(config('summit.timezone'))->afterOrEqual('starts_at'),
                TextInput::make('location')->label('Venue')->maxLength(255)->helperText('Links to Google Maps on the programme page.'),
                TextInput::make('partner')->label('Organiser / partner')->maxLength(160),
                Select::make('status')->options(array_column(ProgramStatus::options(), 'label', 'value'))->required()->native(false)->default(ProgramStatus::Upcoming->value),
                Toggle::make('registration_open')->label('Registration form open')->inline(false),
                TextInput::make('participants')->label('Participants (reported)')->numeric()->minValue(0)
                    ->helperText('Attendance after the event; counts towards "participants reached". Blank counts the online registrations.'),
                MediaPicker::make('cover_image')->label('Cover image')->columnSpanFull(),
                RichEditor::make('description')->label('About this programme')->columnSpanFull()
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h3', 'blockquote', 'undo', 'redo']),
            ]),
            Section::make('Speakers & mentors')->columnSpanFull()->schema([
                Select::make('speakers')->hiddenLabel()->relationship('speakers', 'name', fn ($query) => $query->where('visible', true)->orderBy('name'))
                    ->multiple()->searchable()->preload()
                    ->helperText('Shown under "Organiser / Partner" on the programme page; a click opens the speaker popup. Manage people in Event Management › Speakers & experts.'),
            ]),
            Section::make('Search engines')->columns(2)->columnSpanFull()->collapsed()->schema([
                TextInput::make('seo_title')->label('SEO title')->maxLength(70),
                Textarea::make('seo_description')->label('SEO description')->rows(2)->maxLength(160),
            ]),
        ]);
    }

    /** New rows get this resource's programme type. */
    public static function mutateCreateData(array $data): array
    {
        return [...$data, 'type' => static::type()->value];
    }
}
