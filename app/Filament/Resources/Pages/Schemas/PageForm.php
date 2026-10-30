<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Fields\MediaPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        $count = fn (int $ideal) => fn (?string $state) => mb_strlen((string) $state)." / {$ideal} characters (ideal)";

        return $schema->components([
            Section::make('Page')->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->label('Page name')->helperText('The name shown in the CMS.')->required()->maxLength(255),
                TextInput::make('slug')->label('URL')->disabled()->dehydrated(false)->helperText('Set by the site routes.'),
            ]),
            Section::make('Search & social')->description('Blank fields keep the title, description and share image set in the site code.')->columnSpanFull()->schema([
                TextInput::make('meta_title')->label('Meta title')->maxLength(70)->live(debounce: 400)->helperText($count(60))
                    ->hint('The site adds " · Freelancer Summit Bangladesh" automatically'),
                Textarea::make('meta_description')->label('Meta description')->rows(3)->maxLength(320)->live(debounce: 400)->helperText($count(155)),
                TextInput::make('keywords')->helperText('Comma separated, e.g. freelancer summit, bangladesh, BACCO')->maxLength(255),
                MediaPicker::make('og_image')->label('Social share image (Open Graph)')->helperText('Best at 1200×630. Blank uses the site default.'),
            ]),
        ]);
    }
}
