<?php

namespace App\Filament\Resources\Submissions;

use App\Enums\SponsorshipStatus;
use App\Filament\Resources\Submissions\Pages\ListSponsorshipEnquiries;
use App\Http\Controllers\SubmissionController;
use App\Models\SponsorshipEnquiry;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

/** Sponsorship enquiries (/sponsorship-opportunity): the partnerships pipeline, new → confirmed. */
class SponsorshipEnquiryResource extends SubmissionResource
{
    protected static string $module = 'sponsorships';

    protected static ?string $model = SponsorshipEnquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Leads & CRM';

    protected static ?string $navigationLabel = 'Sponsorship enquiries';

    protected static ?string $pluralModelLabel = 'Sponsorship enquiries';

    protected static ?string $modelLabel = 'sponsorship enquiry';

    protected static ?string $recordTitleAttribute = 'company';

    protected static ?int $navigationSort = 2;

    protected static function statuses(): ?array
    {
        return array_column(SponsorshipStatus::options(), 'label', 'value');
    }

    protected static function exportColumns(): array
    {
        return [
            'Reference' => 'reference', 'Received' => 'created_at', 'Company' => 'company', 'Contact person' => 'contact_person', 'Designation' => 'designation',
            'Email' => 'email', 'Interest' => 'interest', 'Budget' => fn (SponsorshipEnquiry $m) => SubmissionController::BUDGETS[$m->budget] ?? $m->budget,
            'Callback requested' => 'callback_requested', 'Requirements' => 'requirements', 'Message' => 'message',
        ];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('company')->searchable()->sortable()->weight('medium')->wrap(),
            TextColumn::make('contact_person')->label('Contact person')->searchable()->sortable(),
            TextColumn::make('email')->searchable()->copyable()->limit(26)->tooltip(fn ($state) => $state),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('interest')->badge()->color('primary')->formatStateUsing(fn (?string $state) => $state ? str($state)->headline() : null),
            TextColumn::make('budget')->formatStateUsing(fn (?string $state) => SubmissionController::BUDGETS[$state] ?? $state)->toggleable(),
            IconColumn::make('callback_requested')->label('Callback')->boolean()->toggleable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            static::statusFilter(),
            SelectFilter::make('budget')->options(SubmissionController::BUDGETS),
            TernaryFilter::make('callback_requested')->label('Callback requested'),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowSection(),
            Section::make('Enquiry')->columns(2)->columnSpanFull()->schema([
                TextInput::make('company')->required()->maxLength(160),
                TextInput::make('contact_person')->label('Contact person')->required()->maxLength(120),
                TextInput::make('designation')->maxLength(120),
                TextInput::make('email')->email()->required()->maxLength(160),
                TextInput::make('phone')->tel()->required()->maxLength(32),
                TextInput::make('interest')->maxLength(64),
                Select::make('budget')->options(SubmissionController::BUDGETS)->native(false),
                Toggle::make('callback_requested')->label('Callback requested'),
                Textarea::make('requirements')->rows(3)->columnSpanFull(),
                Textarea::make('message')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowEntries(),
            Section::make('Enquiry')->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
                TextEntry::make('company'), TextEntry::make('contact_person')->label('Contact person'),
                TextEntry::make('designation')->placeholder('—'), TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->copyable(), TextEntry::make('interest')->placeholder('—'),
                TextEntry::make('budget')->formatStateUsing(fn (?string $state) => SubmissionController::BUDGETS[$state] ?? $state)->placeholder('—'),
                IconEntry::make('callback_requested')->label('Callback requested')->boolean(),
                TextEntry::make('requirements')->placeholder('—')->columnSpanFull(),
                TextEntry::make('message')->placeholder('—')->columnSpanFull(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSponsorshipEnquiries::route('/')];
    }
}
