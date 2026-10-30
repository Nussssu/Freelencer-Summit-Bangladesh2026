<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Page')->searchable()->weight('medium'),
                TextColumn::make('slug')->label('URL')->searchable()->color('gray')->url(fn (Page $r) => str_contains($r->slug, '{') ? null : url($r->slug), true),
                TextColumn::make('meta_title')->label('Meta title')->placeholder('Default')->limit(40),
                IconColumn::make('og_image')->label('Share image')->boolean()->state(fn (Page $r) => (bool) $r->og_image),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable(),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->recordActions([EditAction::make()]);
    }
}
