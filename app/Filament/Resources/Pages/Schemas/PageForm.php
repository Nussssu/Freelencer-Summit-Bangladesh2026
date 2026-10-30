<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Fields\BlockFields;
use App\Filament\Fields\MediaPicker;
use App\Support\PageBlocks;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        $blocks = PageBlocks::editable((string) $schema->getRecord()?->slug);
        $count = fn (int $ideal) => fn (?string $state) => mb_strlen((string) $state)." / {$ideal} characters (ideal)";

        return $schema->components([
            Section::make('Page')->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->label('Page name')->helperText('The name shown in the CMS.')->required()->maxLength(255),
                TextInput::make('slug')->label('URL')->disabled()->dehydrated(false)->helperText('Set by the site routes.'),
                Toggle::make('is_draft')->label('Draft')->visible((bool) $blocks)
                    ->helperText('While a draft, the site shows the coded content and ignores the blocks below. SEO fields apply either way.'),
            ]),
            Section::make('Content blocks')->columnSpanFull()->visible((bool) $blocks)
                ->description('One block per page section, in the exact shape the page uses. Remove a block to fall back to the coded content for that section.')
                ->schema([
                    Builder::make('sections')->hiddenLabel()->blocks(BlockFields::blocks($blocks))
                        ->blockNumbers(false)->reorderable(false)->collapsible()->collapsed()->addActionLabel('Add a section block'),
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
