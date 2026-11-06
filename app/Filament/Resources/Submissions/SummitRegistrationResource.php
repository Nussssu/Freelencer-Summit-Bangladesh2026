<?php

namespace App\Filament\Resources\Submissions;

use App\Enums\AttendeeType;
use App\Enums\Interest;
use App\Filament\Resources\Submissions\Pages\ListSummitRegistrations;
use App\Models\District;
use App\Models\SummitRegistration;
use App\Filament\Support\SubmissionCsvImport;
use App\Support\Statuses;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class SummitRegistrationResource extends SubmissionResource
{
    protected static string $module = 'registrations';

    protected static ?string $model = SummitRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Registrations';

    protected static ?string $navigationLabel = 'Grand Summit registrations';

    protected static ?string $pluralModelLabel = 'Grand Summit registrations';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static function statuses(): ?array
    {
        return Statuses::REGISTRATION;
    }

    protected static function exportColumns(): array
    {
        return ['Reference' => 'reference', 'Received' => 'created_at', 'Name' => 'name', 'Email' => 'email', 'Organisation' => 'organisation', 'Designation' => 'designation', 'District' => 'district.name', 'Attendee type' => 'attendee_type', 'Interests' => 'interests', 'UTM source' => 'utm_source'];
    }

    public static function importColumns(): array
    {
        return [
            'Organisation' => 'organisation', 'Designation' => 'designation',
            'District' => fn (string $v) => ['district_id' => District::query()->where('name', $v)->orWhere('slug', Str::slug($v))->value('id')],
            'Attendee type' => fn (string $v) => ['attendee_type' => SubmissionCsvImport::pick(array_column(AttendeeType::options(), 'label', 'value'), $v)],
            'Interests' => fn (string $v) => ['interests' => SubmissionCsvImport::pickMany(array_column(Interest::options(), 'label', 'value'), $v)],
        ];
    }

    public static function importDefaults(): array
    {
        return ['edition' => (string) config('summit.edition'), 'attendee_type' => AttendeeType::cases()[0]->value];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable()->sortable(),
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('organisation')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('designation')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('district.name')->label('District')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('attendee_type')->label('Attendee type')->badge()->formatStateUsing(fn (AttendeeType $state) => $state->label()),
            TextColumn::make('interests')->badge()->formatStateUsing(fn (string $state) => Interest::tryFrom($state)?->label() ?? $state)->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            static::statusFilter(),
            SelectFilter::make('attendee_type')->label('Attendee type')->options(array_column(AttendeeType::options(), 'label', 'value'))->native(false)->selectablePlaceholder(),
            static::districtFilter(),
            static::receivedFilter(),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowSection(),
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->email()->required()->maxLength(255),
                TextInput::make('phone')->tel()->required()->maxLength(32),
                Select::make('district_id')->label('District')->relationship('district', 'name')->searchable()->preload(),
                TextInput::make('organisation')->maxLength(255),
                TextInput::make('designation')->maxLength(255),
                Select::make('attendee_type')->label('Attendee type')->options(array_column(AttendeeType::options(), 'label', 'value'))->required()->native(false),
                CheckboxList::make('interests')->options(array_column(Interest::options(), 'label', 'value'))->columns(2),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowEntries(),
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
                TextEntry::make('name'), TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->copyable(), TextEntry::make('district.name')->label('District')->placeholder('—'),
                TextEntry::make('organisation')->placeholder('—'), TextEntry::make('designation')->placeholder('—'),
                TextEntry::make('attendee_type')->label('Attendee type')->formatStateUsing(fn (AttendeeType $state) => $state->label()),
                TextEntry::make('interests')->badge()->formatStateUsing(fn (string $state) => Interest::tryFrom($state)?->label() ?? $state)->placeholder('—'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSummitRegistrations::route('/')];
    }
}
