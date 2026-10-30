<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListBoothApplications;
use App\Models\ExhibitionApplication;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;

class BoothApplicationResource extends SubmissionResource
{
    protected static string $module = 'exhibitions';

    protected static ?string $model = ExhibitionApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Applications';

    protected static ?string $pluralModelLabel = 'Applications';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'booth application';

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('organisation')->searchable()->sortable()->weight('medium')->wrap(),
            TextColumn::make('sector')->searchable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('booth_requirement')->label('Booth requirement')->placeholder('—')->wrap(),
            TextColumn::make('contact_person')->label('Contact person')->searchable()->sortable(),
            TextColumn::make('designation')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('organisation')->required()->maxLength(255),
                TextInput::make('sector')->required()->maxLength(64),
                TextInput::make('booth_requirement')->label('Booth requirement')->maxLength(64),
                TextInput::make('contact_person')->label('Contact person')->required()->maxLength(255),
                TextInput::make('designation')->maxLength(255),
                TextInput::make('email')->email()->required()->maxLength(255),
                TextInput::make('phone')->tel()->required()->maxLength(32),
                Textarea::make('products')->rows(3)->columnSpanFull(),
                Textarea::make('branding_needs')->label('Branding needs')->rows(3)->columnSpanFull(),
                Textarea::make('requirements')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
                TextEntry::make('organisation'), TextEntry::make('sector'),
                TextEntry::make('booth_requirement')->label('Booth requirement')->placeholder('—'), TextEntry::make('contact_person')->label('Contact person'),
                TextEntry::make('designation')->placeholder('—'), TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->copyable(),
            ]),
            Section::make('Details')->columnSpanFull()->schema([
                TextEntry::make('products')->placeholder('—')->prose(),
                TextEntry::make('branding_needs')->label('Branding needs')->placeholder('—')->prose(),
                TextEntry::make('requirements')->placeholder('—')->prose(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListBoothApplications::route('/')];
    }
}
