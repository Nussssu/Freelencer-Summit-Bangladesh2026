<?php

namespace App\Filament\Resources\Submissions;

use App\Enums\AwardStatus;
use App\Filament\Resources\Submissions\Pages\ListAwardNominations;
use App\Models\AwardApplication;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AwardNominationResource extends SubmissionResource
{
    protected static string $module = 'nominations';

    protected static ?string $model = AwardApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?string $navigationLabel = 'Award Nominations';

    protected static ?string $pluralModelLabel = 'Award Nominations';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'nomination';

    private const COLORS = ['submitted' => 'gray', 'under-review' => 'info', 'shortlisted' => 'warning', 'winner' => 'success', 'rejected' => 'danger'];

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable(),
            TextColumn::make('nominee_name')->label('Nominee')->searchable()->weight('medium'),
            TextColumn::make('nominee_email')->label('Email')->searchable()->copyable(),
            TextColumn::make('nominee_phone')->label('Phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('district.name')->label('District')->toggleable(),
            TextColumn::make('category.name')->label('Category')->badge()->color('primary'),
            TextColumn::make('status')->badge()->formatStateUsing(fn (AwardStatus $state) => $state->label())->color(fn (AwardStatus $state) => self::COLORS[$state->value]),
            IconColumn::make('shortlist_published')->label('Shortlist public')->boolean()->toggleable(),
            IconColumn::make('winner_published')->label('Winner public')->boolean()->toggleable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([
                SelectFilter::make('status')->options(array_column(AwardStatus::options(), 'label', 'value')),
                SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                // The jury workflow: the one change staff make to a nomination.
                Action::make('review')->label('Update status')->icon('heroicon-o-flag')
                    ->authorize(fn (AwardApplication $record) => static::canEdit($record))
                    ->fillForm(fn (AwardApplication $record) => $record->only(['status', 'shortlist_published', 'winner_published']))
                    ->schema([
                        Select::make('status')->options(array_column(AwardStatus::options(), 'label', 'value'))->required()->native(false),
                        Toggle::make('shortlist_published')->label('Show on the public shortlist'),
                        Toggle::make('winner_published')->label('Show in the public winners archive'),
                    ])
                    ->action(fn (AwardApplication $record, array $data) => $record->update($data)),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nominee')->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime(),
                TextEntry::make('nominee_name')->label('Name'), TextEntry::make('nominee_email')->label('Email')->copyable(),
                TextEntry::make('nominee_phone')->label('Phone')->copyable(), TextEntry::make('district.name')->label('District')->placeholder('—'),
                TextEntry::make('category.name')->label('Category'), TextEntry::make('status')->formatStateUsing(fn (AwardStatus $state) => $state->label())->badge(),
                TextEntry::make('self_nominated')->label('Nominated by')->formatStateUsing(fn (bool $state, AwardApplication $record) => $state ? 'Self' : trim("{$record->nominator_name} · {$record->nominator_email} · {$record->nominator_phone}", ' ·')),
            ]),
            Section::make('Evidence')->columnSpanFull()->schema([
                TextEntry::make('portfolio_links')->label('Portfolio links')->state(fn (AwardApplication $r) => static::links($r->portfolio_links))->listWithLineBreaks()->placeholder('—'),
                TextEntry::make('marketplace_profiles')->label('Marketplace profiles')->state(fn (AwardApplication $r) => static::links($r->marketplace_profiles))->listWithLineBreaks()->placeholder('—'),
                TextEntry::make('achievements')->placeholder('—')->prose(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListAwardNominations::route('/')];
    }
}
