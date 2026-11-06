<?php

namespace App\Filament\Resources\Submissions;

use App\Enums\ProgramType;
use App\Filament\Resources\Submissions\Pages\ListProgrammeRegistrations;
use App\Models\ProgramRegistration;
use App\Support\Statuses;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Registrations for a divisional summit, district roadshow or campus programme (each programme page's form). */
class ProgrammeRegistrationResource extends SubmissionResource
{
    protected static string $module = 'registrations';

    protected static ?string $model = ProgramRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Registrations';

    protected static ?string $navigationLabel = 'Programme registrations';

    protected static ?string $pluralModelLabel = 'Programme registrations';

    protected static ?string $modelLabel = 'programme registration';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    protected static function statuses(): ?array
    {
        return Statuses::REGISTRATION;
    }

    protected static function exportColumns(): array
    {
        return [
            'Reference' => 'reference', 'Received' => 'created_at', 'Name' => 'name', 'Email' => 'email', 'Institution' => 'institution',
            'Programme' => 'program.title', 'Programme type' => fn (ProgramRegistration $m) => $m->program?->type?->label(),
        ];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('program.title')->label('Programme')->wrap()->sortable(),
            TextColumn::make('program.type')->label('Type')->badge()->formatStateUsing(fn (ProgramType $state) => $state->label()),
            TextColumn::make('institution')->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            static::statusFilter(),
            SelectFilter::make('type')->label('Programme type')->options(array_column(ProgramType::options(), 'label', 'value'))
                ->query(fn (Builder $query, array $data) => $data['value'] ? $query->whereHas('program', fn ($p) => $p->where('type', $data['value'])) : $query),
            SelectFilter::make('program')->label('Programme')->relationship('program', 'title')->searchable()->preload(),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowSection(),
            Section::make('Registrant')->columns(2)->columnSpanFull()->schema([
                Select::make('journey_program_id')->label('Programme')->relationship('program', 'title')->searchable()->preload()->required()->columnSpanFull(),
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('email')->email()->required()->maxLength(160),
                TextInput::make('phone')->tel()->required()->maxLength(32),
                TextInput::make('institution')->maxLength(160),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowEntries(),
            Section::make('Registrant')->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
                TextEntry::make('program.title')->label('Programme'), TextEntry::make('program.type')->label('Type')->formatStateUsing(fn (ProgramType $state) => $state->label()),
                TextEntry::make('name'), TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->copyable(), TextEntry::make('institution')->placeholder('—'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListProgrammeRegistrations::route('/')];
    }
}
