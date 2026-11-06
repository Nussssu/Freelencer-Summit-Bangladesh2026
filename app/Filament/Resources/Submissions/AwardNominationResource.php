<?php

namespace App\Filament\Resources\Submissions;

use App\Enums\AwardStatus;
use App\Filament\Resources\Submissions\Pages\ListAwardNominations;
use App\Http\Controllers\SubmissionController;
use App\Filament\Fields\MediaPicker;
use App\Models\AwardApplication;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AwardNominationResource extends SubmissionResource
{
    protected static string $module = 'nominations';

    protected static ?string $model = AwardApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|UnitEnum|null $navigationGroup = 'Awards Management';

    protected static ?string $navigationLabel = 'Nominations';

    protected static ?string $pluralModelLabel = 'Nominations';

    protected static ?string $recordTitleAttribute = 'nominee_name';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'nomination';

    protected static function statuses(): ?array
    {
        return array_column(AwardStatus::options(), 'label', 'value');
    }

    protected static function exportColumns(): array
    {
        return ['Reference' => 'reference', 'Received' => 'created_at', 'Edition' => 'edition', 'Category' => 'category.name', 'Nominee' => 'nominee_name', 'Nominee email' => 'nominee_email', 'Self-nominated' => 'self_nominated', 'Nominator' => 'nominator_name', 'Nominator email' => 'nominator_email', 'District' => 'district.name', 'Headline' => 'headline', 'Score' => 'score', 'Shortlist public' => 'shortlist_published', 'Winner public' => 'winner_published', 'Documents' => fn ($m) => $m->files()->count()];
    }

    protected static function columns(): array
    {
        return [
            TextColumn::make('reference')->fontFamily('mono')->searchable()->sortable(),
            TextColumn::make('nominee_name')->label('Nominee')->searchable()->sortable()->weight('medium'),
            TextColumn::make('nominee_email')->label('Email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state)->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('nominee_phone')->label('Phone')->toggleable(isToggledHiddenByDefault: true), // decrypted by the model cast
            TextColumn::make('district.name')->label('District')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('category.name')->label('Category')->badge()->color('primary'),
            TextColumn::make('score')->label('Score')->numeric()->sortable()->placeholder('—'),
            TextColumn::make('edition')->sortable()->toggleable(isToggledHiddenByDefault: true),
            IconColumn::make('shortlist_published')->label('Shortlist public')->boolean()->toggleable(isToggledHiddenByDefault: true),
            IconColumn::make('winner_published')->label('Winner public')->boolean()->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->filters([
                static::statusFilter(),
                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('edition')->options(fn () => AwardApplication::query()->distinct()->orderByDesc('edition')->pluck('edition', 'edition')->all()),
            ])
            ->recordActions([
                // One click moves a nomination along the jury workflow and publishes it on the site.
                Action::make('shortlist')->label('Shortlist')->icon('heroicon-o-star')->color('info')->iconButton()->tooltip('Shortlist and publish')
                    ->visible(fn (AwardApplication $r) => static::can('update') && $r->status !== AwardStatus::Shortlisted && $r->status !== AwardStatus::Winner)
                    ->requiresConfirmation()->modalDescription('Marks the nomination Shortlisted and shows it on the public shortlist.')
                    ->action(fn (AwardApplication $r) => $r->update(['status' => AwardStatus::Shortlisted, 'shortlist_published' => true])),
                Action::make('winner')->label('Winner')->icon('heroicon-o-trophy')->color('success')->iconButton()->tooltip('Mark winner and publish')
                    ->visible(fn (AwardApplication $r) => static::can('update') && $r->status !== AwardStatus::Winner)
                    ->requiresConfirmation()->modalDescription('Marks the nomination Winner and adds it to the public winners archive.')
                    ->action(fn (AwardApplication $r) => $r->update(['status' => AwardStatus::Winner, 'winner_published' => true])),
                ViewAction::make()->iconButton()->tooltip('View'),
                EditAction::make()->iconButton()->tooltip('Edit'),
                DeleteAction::make()->iconButton()->tooltip('Delete'),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowSection(),
            Section::make()->columns(2)->columnSpanFull()->schema([
                Select::make('award_category_id')->label('Category')->relationship('category', 'name')->required(),
                Toggle::make('shortlist_published')->label('Show on the public shortlist'),
                Toggle::make('winner_published')->label('Show in the public winners archive'),
                TextInput::make('score')->label('Jury score (0–100)')->numeric()->minValue(0)->maxValue(100),
                MediaPicker::make('photo')->label('Showcase photo')->helperText('Portrait on the public shortlist and winners pages. Upload in Content Management › Media Library.'),
                TextInput::make('nominee_name')->label('Nominee')->required()->maxLength(255),
                TextInput::make('nominee_email')->label('Email')->email()->required()->maxLength(255),
                TextInput::make('nominee_phone')->label('Phone')->tel()->required()->maxLength(32),
                Select::make('district_id')->label('District')->relationship('district', 'name')->searchable()->preload(),
                TextInput::make('headline')->label('Professional title')->maxLength(160),
                Select::make('experience')->label('Freelancing experience')->options(SubmissionController::AWARD_EXPERIENCE)->native(false),
                Textarea::make('bio')->label('Short bio')->rows(3)->maxLength(300)->columnSpanFull(),
                Textarea::make('achievements')->rows(4)->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            static::workflowEntries(),
            Section::make('Nominee')->columns(2)->columnSpanFull()->schema([
                TextEntry::make('reference'), TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A'),
                TextEntry::make('nominee_name')->label('Name'), TextEntry::make('nominee_email')->label('Email')->copyable(),
                TextEntry::make('nominee_phone')->label('Phone')->copyable(), TextEntry::make('district.name')->label('District')->placeholder('—'),
                TextEntry::make('category.name')->label('Category'), TextEntry::make('score')->label('Jury score')->placeholder('Not scored'),
                TextEntry::make('headline')->label('Professional title')->placeholder('—'),
                TextEntry::make('experience')->label('Freelancing experience')->formatStateUsing(fn (string $state) => SubmissionController::AWARD_EXPERIENCE[$state] ?? $state)->placeholder('—'),
                TextEntry::make('bio')->label('Short bio')->placeholder('—')->columnSpanFull(),
                TextEntry::make('self_nominated')->label('Nominated by')->formatStateUsing(fn (bool $state, AwardApplication $record) => $state ? 'Self' : trim("{$record->nominator_name} · {$record->nominator_email} · {$record->nominator_phone}", ' ·')),
            ]),
            Section::make('Supporting documents')->columnSpanFull()->schema([
                TextEntry::make('documents')->hiddenLabel()->html()->placeholder('No documents uploaded.')
                    ->state(fn (AwardApplication $r) => $r->files->map(fn ($f) => '<a href="'.e(route('admin.award-files.show', $f)).'" target="_blank" rel="noopener" style="color:#087f8c;text-decoration:underline">'.e($f->original_name).'</a> <span style="color:#6b7280">('.e(strtoupper(pathinfo($f->original_name, PATHINFO_EXTENSION))).', '.number_format($f->size / 1024).' KB)</span>')->implode('<br>') ?: null),
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
