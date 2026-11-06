<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListCampusAmbassadors;
use App\Models\AmbassadorLead;
use App\Models\Campus;
use App\Support\Statuses;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Campus Ambassador applications (the campus programmes page form): review, approve or reject. Approved ambassadors
 * have their own tab, so the team can work from the approved list.
 */
class CampusAmbassadorResource extends SubmissionResource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $module = 'memberships';

    protected static ?string $model = AmbassadorLead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Registrations';

    protected static ?string $navigationLabel = 'Campus ambassadors';

    protected static ?string $pluralModelLabel = 'Campus ambassadors';

    protected static ?string $modelLabel = 'campus ambassador';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    protected static function statuses(): ?array
    {
        return Statuses::APPLICATION;
    }

    /** Institution is required; a name matching a listed campus also links that campus. */
    public static function importColumns(): array
    {
        return [
            'Institution' => fn (string $v) => ['institution' => $v, 'campus_id' => Campus::query()->where('name', $v)->value('id')],
            'Department' => 'department', 'Motivation' => 'motivation',
        ];
    }

    protected static function exportColumns(): array
    {
        return [
            'Reference' => 'reference', 'Received' => 'created_at', 'Name' => 'name', 'Email' => 'email',
            'Institution' => fn (AmbassadorLead $m) => $m->campus?->name ?? $m->institution, 'Department' => 'department', 'Motivation' => 'motivation',
        ];
    }

    /** @return array<string, Tab> */
    public static function tabs(): array
    {
        return [
            'all' => Tab::make('All applications'),
            'approved' => Tab::make('Approved ambassadors')->icon('heroicon-o-check-badge')->modifyQueryUsing(fn ($query) => $query->where('status', 'approved')),
        ];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('institution')->label('Institution')->state(fn (AmbassadorLead $r) => $r->campus?->name ?? $r->institution)->searchable(['institution'])->wrap(),
            TextColumn::make('department')->searchable()->toggleable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            static::statusFilter(),
            SelectFilter::make('campus')->relationship('campus', 'name')->searchable()->preload()->native(false)->selectablePlaceholder(),
            static::receivedFilter(),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowSection(),
            Section::make('Applicant')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('email')->email()->required()->maxLength(160),
                TextInput::make('phone')->tel()->required()->maxLength(32),
                Select::make('campus_id')->label('Campus')->relationship('campus', 'name')->searchable()->preload(),
                TextInput::make('institution')->label('Institution (as typed)')->maxLength(160),
                TextInput::make('department')->maxLength(120),
                Textarea::make('motivation')->rows(4)->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowEntries(),
            Section::make('Applicant')->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
                TextEntry::make('name'), TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->copyable(), TextEntry::make('institution')->state(fn (AmbassadorLead $r) => $r->campus?->name ?? $r->institution)->placeholder('—'),
                TextEntry::make('department')->placeholder('—'),
                TextEntry::make('motivation')->placeholder('—')->columnSpanFull(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCampusAmbassadors::route('/')];
    }
}
