<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListExhibitionInquiries;
use App\Models\ExhibitEnquiry;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class ExhibitionInquiryResource extends SubmissionResource
{
    protected static string $module = 'exhibitions';

    protected static ?string $model = ExhibitEnquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Messages';

    protected static ?string $navigationLabel = 'Exhibition Inquiries';

    protected static ?string $pluralModelLabel = 'Exhibition Inquiries';

    protected static ?string $recordTitleAttribute = 'company_name';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'exhibition inquiry';

    // The live "Enquire to exhibit" popup (landing page). It asks no booth size; booth requirements come from the
    // /exhibition/apply form (Booth Applications) once that page is built.
    protected static function columns(): array
    {
        return [
            TextColumn::make('company_name')->label('Organisation')->searchable()->sortable()->weight('medium')->wrap(),
            TextColumn::make('industry')->label('Sector')->searchable()->wrap(),
            TextColumn::make('full_name')->label('Contact person')->searchable()->sortable(),
            TextColumn::make('designation')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('head_office')->label('Head office')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('website')->url(fn (ExhibitEnquiry $r) => $r->website, true)->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('company_name')->label('Organisation')->required()->maxLength(160),
                TextInput::make('industry')->label('Sector')->required()->maxLength(120),
                TextInput::make('website')->url()->maxLength(255),
                TextInput::make('head_office')->label('Head office')->required()->maxLength(160),
                TextInput::make('full_name')->label('Contact person')->required()->maxLength(120),
                TextInput::make('designation')->required()->maxLength(120),
                TextInput::make('email')->email()->required()->maxLength(160),
                TextInput::make('phone')->tel()->required()->maxLength(32),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextEntry::make('company_name')->label('Organisation'), TextEntry::make('industry')->label('Sector'),
                TextEntry::make('website')->placeholder('—'), TextEntry::make('head_office')->label('Head office'),
                TextEntry::make('full_name')->label('Contact person'), TextEntry::make('designation'),
                TextEntry::make('email')->copyable(), TextEntry::make('phone')->copyable(),
                TextEntry::make('reference')->placeholder('—'), TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListExhibitionInquiries::route('/')];
    }
}
