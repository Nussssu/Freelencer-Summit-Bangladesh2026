<?php

namespace App\Filament\Resources\Submissions;

use App\Enums\AttendeeType;
use App\Enums\Interest;
use App\Filament\Resources\Submissions\Pages\ListSummitRegistrations;
use App\Models\SummitRegistration;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SummitRegistrationResource extends SubmissionResource
{
    protected static string $module = 'registrations';

    protected static ?string $model = SummitRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Summit Registrations';

    protected static ?string $pluralModelLabel = 'Summit Registrations';

    protected static ?int $navigationSort = 2;

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable(),
            TextColumn::make('name')->searchable()->weight('medium'),
            TextColumn::make('email')->searchable()->copyable(),
            TextColumn::make('phone')->toggleable(), // decrypted by the model cast
            TextColumn::make('organisation')->searchable()->toggleable(),
            TextColumn::make('designation')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('district.name')->label('District')->toggleable(),
            TextColumn::make('attendee_type')->label('Attendee type')->badge()->formatStateUsing(fn (AttendeeType $state) => $state->label()),
            TextColumn::make('interests')->badge()->formatStateUsing(fn (string $state) => Interest::tryFrom($state)?->label() ?? $state)->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)->filters([
            SelectFilter::make('attendee_type')->label('Attendee type')->options(array_column(AttendeeType::options(), 'label', 'value')),
            SelectFilter::make('district')->relationship('district', 'name')->searchable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime(),
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
