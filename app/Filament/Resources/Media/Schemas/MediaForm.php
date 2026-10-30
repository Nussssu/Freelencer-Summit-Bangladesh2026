<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['lg' => 5])->columnSpanFull()->schema([
                Section::make()->columnSpan(['lg' => 3])->schema([
                    ImageEntry::make('preview')->hiddenLabel()->state(fn (Media $record) => url($record->url))->imageHeight('auto')->extraImgAttributes(['style' => 'max-height:60vh;width:100%;object-fit:contain;border-radius:8px;background:#f5f2e9'])
                        ->visible(fn (?Media $record) => $record?->isImage()),
                    TextEntry::make('mime')->hiddenLabel()->visible(fn (?Media $record) => ! $record?->isImage())
                        ->formatStateUsing(fn (Media $record) => "No preview for {$record->mime}."),
                ]),
                Section::make('Details')->columnSpan(['lg' => 2])->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('alt')->label('Alt text')->helperText('Describe the image for screen readers and search engines.')->maxLength(255),
                    TextEntry::make('url')->label('File URL')->copyable()->state(fn (Media $record) => url($record->url)),
                    TextEntry::make('path')->label('Stored at')->state(fn (Media $record) => 'public/media/'.$record->path)->color('gray'),
                    TextEntry::make('size')->state(fn (Media $record) => trim(($record->width ? "{$record->width}×{$record->height} px · " : '').Number::fileSize($record->size)))->color('gray'),
                ]),
            ]),
        ]);
    }
}
