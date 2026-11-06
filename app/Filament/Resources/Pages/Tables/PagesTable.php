<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use App\Support\Access;
use App\Support\PageBlocks;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Pages list with icon actions that act on the live site: Copy serves a duplicate at a new URL (same layout, its own
 * blocks and SEO), View opens the page, Edit changes it, Delete takes the page offline (404) until it is restored from
 * the "Deleted pages" filter. Copies can also be removed for good. The homepage cannot be deleted.
 */
class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Page')->searchable()->weight('medium')
                    ->description(fn (Page $r) => $r->source ? "Copy of {$r->source}" : null),
                TextColumn::make('slug')->label('URL')->searchable()->color('gray')->url(fn (Page $r) => self::live($r) ? url($r->slug) : null, true),
                TextColumn::make('status')->badge()
                    ->state(fn (Page $r) => $r->trashed() ? 'Offline' : ($r->sections ? ($r->is_draft ? 'Draft' : 'Published') : 'SEO only'))
                    ->color(fn (string $state) => ['Published' => 'success', 'Draft' => 'warning', 'Offline' => 'danger'][$state] ?? 'gray'),
                TextColumn::make('meta_title')->label('Meta title')->placeholder('Default')->limit(40)->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('og_image')->label('Share image')->boolean()->state(fn (Page $r) => (bool) $r->og_image),
                TextColumn::make('updated_at')->label('Last updated')->since()->sortable(),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->filters([TrashedFilter::make()->label('Deleted pages')])
            ->recordActions([
                Action::make('copy')->label('Copy')->tooltip('Copy')->icon(Heroicon::OutlinedDocumentDuplicate)->iconButton()->color('gray')
                    ->visible(fn (Page $r) => ! $r->trashed() && PageBlocks::codedHero($r->source ?? $r->slug) && auth()->user()?->can(Access::permission('create', 'pages')))
                    ->modalHeading(fn (Page $r) => "Copy {$r->title}")
                    ->modalDescription('The copy goes live at its own URL with the same layout and content, which you can then edit separately.')
                    ->modalSubmitActionLabel('Create copy')
                    ->fillForm(fn (Page $r) => ['title' => "{$r->title} (copy)", 'slug' => "{$r->slug}-copy"])
                    ->schema([
                        TextInput::make('title')->label('Page name')->required()->maxLength(255),
                        TextInput::make('slug')->label('URL')->required()->maxLength(255)
                            ->helperText('Lowercase letters, numbers and dashes, e.g. /about-2026.')
                            ->dehydrateStateUsing(fn (string $state) => self::path($state))
                            ->rules([
                                'regex:#^/?[a-z0-9]+(-[a-z0-9]+)*(/[a-z0-9]+(-[a-z0-9]+)*)*/?$#',
                                fn () => function (string $attribute, mixed $value, Closure $fail) {
                                    $slug = self::path((string) $value);
                                    if (Page::withTrashed()->where('slug', $slug)->exists()) {
                                        $fail('A page already uses this URL.');
                                    } elseif (! rescue(fn () => Route::getRoutes()->match(Request::create($slug))->isFallback, true, report: false)) {
                                        $fail('This URL belongs to another part of the site.');
                                    }
                                },
                            ]),
                    ])
                    ->action(function (Page $r, array $data) {
                        $copy = Page::create([
                            ...$r->only(['sections', 'is_draft', 'meta_title', 'meta_description', 'keywords', 'og_image']),
                            'title' => $data['title'],
                            'slug' => $data['slug'],
                            'source' => $r->source ?? $r->slug,
                        ]);
                        Notification::make()->success()->title('Copy created')->body("Live at {$copy->slug}")
                            ->actions([Action::make('open')->label('Open page')->url(url($copy->slug), true)])->send();
                    }),
                Action::make('view')->label('View')->tooltip('View on site')->icon(Heroicon::OutlinedEye)->iconButton()->color('gray')
                    ->visible(fn (Page $r) => self::live($r))
                    ->url(fn (Page $r) => url($r->slug), true),
                EditAction::make()->iconButton()->tooltip('Edit'),
                DeleteAction::make()->iconButton()->tooltip('Delete')
                    ->hidden(fn (Page $r) => $r->slug === '/')
                    ->modalHeading(fn (Page $r) => "Delete {$r->title}?")
                    ->modalDescription(fn (Page $r) => (str_contains($r->slug, '{') ? 'Every page at this address goes' : 'The page goes')
                        .' offline (404) until you restore it from the "Deleted pages" filter. Menu and page links to it are not removed.'),
                RestoreAction::make()->iconButton()->tooltip('Restore'),
                ForceDeleteAction::make()->iconButton()->tooltip('Delete for good')->visible(fn (Page $r) => $r->trashed() && $r->source),
            ]);
    }

    /** On the site now: not deleted and a single URL (detail patterns like /speaker/{slug} have no one page to open). */
    private static function live(Page $r): bool
    {
        return ! $r->trashed() && ! str_contains($r->slug, '{');
    }

    private static function path(string $value): string
    {
        return '/'.trim(strtolower($value), '/ ');
    }
}
