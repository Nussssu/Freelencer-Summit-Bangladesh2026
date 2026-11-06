<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use App\Filament\Resources\EmailTemplates\EmailPreview;
use App\Models\EmailTemplate;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** One card per email: name, who receives it, subject, on/off and last edit, with Preview and Edit. */
class EmailTemplatesTable
{
    public const AUDIENCE = [
        'admin_notification' => 'To the admin inboxes, for every form',
        'registration_confirmed' => 'To people who register (summit, programmes)',
        'user_confirmation' => 'To people who send any other form',
        'forum_approved' => 'To forum applicants when you approve them',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('name')->weight(FontWeight::Bold)->size(TextSize::Large),
                        TextColumn::make('enabled')->badge()->grow(false)
                            ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Off')
                            ->color(fn (bool $state) => $state ? 'success' : 'gray'),
                    ]),
                    TextColumn::make('key')->color('gray')->icon('heroicon-o-user-group')
                        ->formatStateUsing(fn (string $state) => self::AUDIENCE[$state] ?? 'To the person who submitted the form'),
                    TextColumn::make('subject')->icon('heroicon-o-envelope')->wrap()->limit(90),
                    TextColumn::make('updated_at')->color('gray')->size(TextSize::ExtraSmall)
                        ->formatStateUsing(fn ($state) => 'Updated '.$state?->diffForHumans()),
                ])->space(3),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginated(false)
            ->recordUrl(null)
            ->recordActions([EmailPreview::action(), EditAction::make()->button()])
            ->defaultSort(fn ($query) => $query->orderByRaw("case `key` when 'registration_confirmed' then 1 when 'user_confirmation' then 2 when 'forum_approved' then 3 else 4 end"));
    }
}
