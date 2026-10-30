<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Models\Lead;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LeadsTable
{
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
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => Lead::STATUSES[$state] ?? $state)->color(fn (string $state) => self::COLORS[$state] ?? 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')->options(fn () => Lead::query()->distinct()->pluck('type')->mapWithKeys(fn ($t) => [$t => Str::headline($t)])->all()),
                SelectFilter::make('status')->options(Lead::STATUSES),
            ])
            ->recordActions([EditAction::make()->label('Open')])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('status')->label('Set status')->icon('heroicon-o-tag')
                        ->schema([Select::make('status')->options(Lead::STATUSES)->required()])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
