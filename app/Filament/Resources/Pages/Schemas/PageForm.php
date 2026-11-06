<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Fields\BlockFields;
use App\Filament\Fields\MediaPicker;
use App\Support\PageBlocks;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        // A copy edits the blocks of the page it was copied from.
        $slug = (string) ($schema->getRecord()?->source ?? $schema->getRecord()?->slug);
        $blocks = PageBlocks::editable($slug);
        $hero = PageBlocks::codedHero($slug);
        $editable = $blocks || $hero;
        $count = fn (int $ideal) => fn (?string $state) => mb_strlen((string) $state)." / {$ideal} characters (ideal)";

        return $schema->components([
            Section::make('Page')->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->label('Page name')->helperText('The name shown in the CMS.')->required()->maxLength(255),
                TextInput::make('slug')->label('URL')->disabled()->dehydrated(false)
                    ->helperText(fn ($record) => $record?->source ? "A copy of {$record->source}, with that page's layout." : 'Set by the site routes.'),
                Toggle::make('is_draft')->label('Draft')->visible($editable)
                    ->helperText('While a draft, the site shows the coded content and ignores the blocks below. SEO fields apply either way.'),
            ]),
            Section::make('Content blocks')->columnSpanFull()->visible($editable)
                ->description('One block per page section, in the exact shape the page uses: the hero (title, image, buttons) first, then each section. Remove a block to fall back to the coded content for that section.')
                ->schema([
                    Builder::make('sections')->hiddenLabel()->blocks([...($hero ? [self::hero($hero, PageBlocks::plain($slug))] : []), ...BlockFields::blocks($blocks)])
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

    /** The page hero: title (with the part shown in colour), intro, background image and buttons. */
    private static function hero(array $coded, bool $plain = false): Block
    {
        return Block::make('hero')->label('Hero')->maxItems(1)->columns(2)->schema([
            TextInput::make('title')->label('Title')->required()->default($coded['title'])->columnSpanFull(),
            TextInput::make('emphasis')->label('Coloured part of the title')->default($coded['emphasis'])
                ->helperText('Words from the title shown in colour, e.g. "Roadshows".'),
            MediaPicker::make('image')->label('Background image')->default($coded['image'])->hidden($plain),
            Textarea::make('intro')->label('Intro')->rows(2)->autosize()->default($coded['intro'])->columnSpanFull()
                ->helperText('Shown on pages whose hero has an intro line.'),
            Repeater::make('actions')->label('Buttons')->default($coded['actions'])->columns(3)->columnSpanFull()->hidden($plain)
                ->reorderable()->collapsible()->addActionLabel('Add a button')
                ->itemLabel(fn (array $state) => $state['label'] ?? null)
                ->schema([
                    TextInput::make('label')->label('Text')->required(),
                    TextInput::make('href')->label('Link')->required()->helperText('A page path like /register, or a full URL.'),
                    Select::make('tone')->label('Style')->options(['gold' => 'Gold (main)', 'ghost' => 'Outline'])->default('gold')->required(),
                ]),
        ]);
    }
}
