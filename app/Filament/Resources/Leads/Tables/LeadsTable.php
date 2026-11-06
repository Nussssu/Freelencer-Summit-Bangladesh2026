<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Filament\Support\CsvExport;
use App\Models\Lead;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LeadsTable
{
    /** CSV columns; phones stay out of exports, as everywhere. */
    private const EXPORT = ['Received' => 'created_at', 'Type' => 'type', 'Reference' => 'reference', 'Name' => 'name', 'Email' => 'email', 'Status' => 'status', 'Follow up' => 'follow_up_at', 'Notes' => 'notes'];

    public const COLORS = ['new' => 'warning', 'contacted' => 'info', 'closed' => 'success', 'spam' => 'gray'];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Received')->dateTime('d M Y, h:i A')->sortable(),
                TextColumn::make('type')->badge()->formatStateUsing(fn (string $state) => Str::headline($state))->color('primary'),
                TextColumn::make('name')->searchable()->sortable()->weight('medium')->placeholder('—'),
                TextColumn::make('email')->searchable()->sortable()->copyable()->limit(26)->tooltip(fn ($state) => $state)->placeholder('—'),
                TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reference')->searchable()->sortable()->fontFamily('mono')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('follow_up_at')->label('Follow up')->dateTime('d M, h:i A')->sortable()->placeholder('—')->color(fn (Lead $r) => $r->follow_up_at?->isPast() && ! in_array($r->status, ['closed', 'spam'], true) ? 'danger' : null),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => Lead::STATUSES[$state] ?? $state)->color(fn (string $state) => self::COLORS[$state] ?? 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')->options(fn () => Lead::query()->distinct()->pluck('type')->mapWithKeys(fn ($t) => [$t => Str::headline($t)])->all()),
                SelectFilter::make('status')->options(Lead::STATUSES),
                Filter::make('follow_up_due')->label('Follow-up due')->toggle()
                    ->query(fn ($query) => $query->whereNotNull('follow_up_at')->where('follow_up_at', '<=', now())->whereNotIn('status', ['closed', 'spam'])),
            ])
            ->headerActions([CsvExport::action('Leads', self::EXPORT)])
            ->recordActions([EditAction::make()->label('Open')])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('status')->label('Set status')->icon('heroicon-o-tag')
                        ->schema([Select::make('status')->options(Lead::STATUSES)->required()])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
                        ->deselectRecordsAfterCompletion(),
                    CsvExport::bulk('Leads', self::EXPORT),
                ]),
            ]);
    }
}
