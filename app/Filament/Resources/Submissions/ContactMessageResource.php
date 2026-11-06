<?php

namespace App\Filament\Resources\Submissions;

use App\Filament\Resources\Submissions\Pages\ListContactMessages;
use App\Models\ContactMessage;
use App\Support\Statuses;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class ContactMessageResource extends SubmissionResource
{
    protected static string $module = 'messages';

    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Messages';

    protected static ?string $navigationLabel = 'Contact messages';

    protected static ?string $pluralModelLabel = 'Contact messages';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'contact message';

    protected static function statuses(): ?array
    {
        return Statuses::MESSAGE;
    }

    protected static function exportColumns(): array
    {
        return ['Reference' => 'reference', 'Received' => 'created_at', 'Topic' => 'topic', 'Name' => 'name', 'Email' => 'email', 'Message' => 'message'];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable()->sortable(),
            TextColumn::make('name')->searchable()->sortable()->weight('medium'),
            TextColumn::make('email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state),
            TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('topic')->badge()->searchable()->sortable(),
            TextColumn::make('message')->limit(50)->wrap(),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowSection(),
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->email()->required()->maxLength(255),
                TextInput::make('phone')->tel()->maxLength(40),
                TextInput::make('topic')->required()->maxLength(32),
                Textarea::make('message')->required()->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowEntries(),
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextEntry::make('name'), TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->copyable(), TextEntry::make('topic')->badge(),
                TextEntry::make('reference')->placeholder('—'), TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
                TextEntry::make('message')->columnSpanFull(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListContactMessages::route('/')];
    }
}
