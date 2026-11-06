<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListForumMemberships;
use App\Filament\Support\SubmissionCsvImport;
use App\Http\Controllers\SubmissionController;
use App\Models\District;
use App\Models\ForumApplication;
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

/** BACCO Freelancer Forum membership applications (/forum/register): review and approve. NID stays encrypted. */
class ForumMembershipResource extends SubmissionResource
{
    protected static string $module = 'memberships';

    protected static ?string $model = ForumApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Registrations';

    protected static ?string $navigationLabel = 'Forum memberships';

    protected static ?string $pluralModelLabel = 'Forum memberships';

    protected static ?string $modelLabel = 'forum membership';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    /** One-click Approve (emails the applicant this template) and Reject. */
    protected static ?string $approvalTemplate = 'forum_approved';

    protected static function statuses(): ?array
    {
        return Statuses::APPLICATION;
    }

    public static function importColumns(): array
    {
        return [
            'District' => fn (string $v) => ['district_id' => District::query()->where('name', $v)->orWhere('slug', Str::slug($v))->value('id')],
            'Nationality' => fn (string $v) => ['nationality' => SubmissionCsvImport::pick(SubmissionController::NATIONALITIES, $v)],
            'Profile type' => fn (string $v) => ['profile_type' => SubmissionCsvImport::pick(SubmissionController::PROFILE_TYPES, $v)],
            'Experience' => fn (string $v) => ['experience_level' => SubmissionCsvImport::pick(SubmissionController::EXPERIENCE, $v)],
            'Skills' => fn (string $v) => ['skills' => SubmissionCsvImport::pickMany(SubmissionController::SKILLS, $v)],
            'Services' => 'services',
            'Availability' => fn (string $v) => ['availability' => SubmissionCsvImport::pick(SubmissionController::AVAILABILITY, $v)],
            'Marketplace profiles' => fn (string $v) => ['marketplace_profiles' => array_values(array_filter(preg_split('/\s*;\s*/', $v)))],
        ];
    }

    public static function importDefaults(): array
    {
        return ['experience_level' => array_key_first(SubmissionController::EXPERIENCE)];
    }

    protected static function exportColumns(): array
    {
        return [
            'Reference' => 'reference', 'Received' => 'created_at', 'Name' => 'name', 'Email' => 'email', 'District' => 'district.name',
            'Nationality' => fn ($m) => SubmissionController::NATIONALITIES[$m->nationality] ?? $m->nationality,
            'Profile type' => fn ($m) => SubmissionController::PROFILE_TYPES[$m->profile_type] ?? $m->profile_type,
            'Experience' => fn ($m) => SubmissionController::EXPERIENCE[$m->experience_level] ?? $m->experience_level,
            'Skills' => fn ($m) => array_map(fn ($s) => SubmissionController::SKILLS[$s] ?? $s, (array) $m->skills),
            'Services' => 'services',
            'Availability' => fn ($m) => SubmissionController::AVAILABILITY[$m->availability] ?? $m->availability,
            'Marketplace profiles' => fn ($m) => static::links($m->marketplace_profiles),
        ];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('district.name')->label('District')->sortable(),
            TextColumn::make('profile_type')->label('Profile')->badge()->formatStateUsing(fn (?string $state) => SubmissionController::PROFILE_TYPES[$state] ?? $state),
            TextColumn::make('experience_level')->label('Experience')->formatStateUsing(fn (?string $state) => SubmissionController::EXPERIENCE[$state] ?? $state)->toggleable(),
            TextColumn::make('skills')->badge()->formatStateUsing(fn (string $state) => SubmissionController::SKILLS[$state] ?? $state)->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            static::statusFilter(),
            SelectFilter::make('profile_type')->label('Profile type')->options(SubmissionController::PROFILE_TYPES)->native(false)->selectablePlaceholder(),
            SelectFilter::make('experience_level')->label('Experience')->options(SubmissionController::EXPERIENCE)->native(false)->selectablePlaceholder(),
            static::districtFilter(),
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
                Select::make('district_id')->label('District')->relationship('district', 'name')->searchable()->preload(),
                Select::make('nationality')->options(SubmissionController::NATIONALITIES)->native(false),
                Select::make('profile_type')->label('Profile type')->options(SubmissionController::PROFILE_TYPES)->native(false),
                Select::make('experience_level')->label('Experience')->options(SubmissionController::EXPERIENCE)->native(false),
                Select::make('availability')->options(SubmissionController::AVAILABILITY)->native(false),
                CheckboxList::make('skills')->options(SubmissionController::SKILLS)->columns(2)->columnSpanFull(),
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
                TextEntry::make('phone')->copyable(), TextEntry::make('district.name')->label('District')->placeholder('—'),
                TextEntry::make('nationality')->formatStateUsing(fn (?string $state) => SubmissionController::NATIONALITIES[$state] ?? $state)->placeholder('—'),
                TextEntry::make('nid')->label('NID')->placeholder('Not given'), // decrypted by the model cast
                TextEntry::make('profile_type')->label('Profile type')->formatStateUsing(fn (?string $state) => SubmissionController::PROFILE_TYPES[$state] ?? $state)->placeholder('—'),
                TextEntry::make('experience_level')->label('Experience')->formatStateUsing(fn (?string $state) => SubmissionController::EXPERIENCE[$state] ?? $state)->placeholder('—'),
                TextEntry::make('availability')->formatStateUsing(fn (?string $state) => SubmissionController::AVAILABILITY[$state] ?? $state)->placeholder('—'),
                TextEntry::make('skills')->badge()->formatStateUsing(fn (string $state) => SubmissionController::SKILLS[$state] ?? $state)->placeholder('—'),
                TextEntry::make('services')->placeholder('—')->columnSpanFull(),
                TextEntry::make('marketplace_profiles')->label('Marketplace profiles')->state(fn (ForumApplication $r) => static::links($r->marketplace_profiles))->listWithLineBreaks()->placeholder('—')->columnSpanFull(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListForumMemberships::route('/')];
    }
}
