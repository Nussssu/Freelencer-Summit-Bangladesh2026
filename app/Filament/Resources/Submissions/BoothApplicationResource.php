<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListBoothApplications;
use App\Models\ExhibitionApplication;
use BackedEnum;
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

    protected static ?string $navigationLabel = 'Booth Applications';

    protected static ?string $pluralModelLabel = 'Booth Applications';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'booth application';

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable(),
            TextColumn::make('organisation')->searchable()->weight('medium'),
            TextColumn::make('sector')->searchable(),
            TextColumn::make('booth_requirement')->label('Booth requirement')->placeholder('—'),
            TextColumn::make('contact_person')->label('Contact person')->searchable(),
            TextColumn::make('designation')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('email')->searchable()->copyable(),
            TextColumn::make('phone')->toggleable(), // decrypted by the model cast
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime(),
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
