<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->weight('medium'),
                TextColumn::make('subject')->color('gray'),
                IconColumn::make('enabled')->boolean(),
                TextColumn::make('updated_at')->label('Updated')->since(),
            ])
            ->paginated(false)
            ->recordActions([EditAction::make()]);
    }
}
