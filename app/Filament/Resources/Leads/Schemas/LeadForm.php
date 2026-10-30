<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Lead;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['lg' => 3])->columnSpanFull()->schema([
                Section::make('Submission')->columnSpan(['lg' => 2])->schema([
                    TextEntry::make('type')->formatStateUsing(fn (string $state) => Str::headline($state))->badge(),
                    TextEntry::make('created_at')->label('Received')->dateTime('j M Y, H:i'),
                    KeyValueEntry::make('data')->hiddenLabel()->keyLabel('Field')->valueLabel('Value')
                        ->state(fn (Lead $record) => collect($record->data)->reject(fn ($v) => $v === null || $v === '')
                            ->mapWithKeys(fn ($v, $k) => [Str::headline($k) => is_scalar($v) ? (string) $v : json_encode($v)])->all()),
                ]),
                Section::make('Follow-up')->columnSpan(['lg' => 1])->schema([
                    Select::make('status')->options(Lead::STATUSES)->required()->native(false),
                    Textarea::make('notes')->rows(6)->helperText('Internal only. Never emailed.'),
                ]),
            ]),
        ]);
    }
}
