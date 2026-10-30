<?php

namespace App\Filament\Resources\Media\Tables;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Js;
use Illuminate\Support\Number;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        $grid = ($table->getLivewire()->display ?? 'grid') === 'grid';
        $thumb = ImageColumn::make('preview')->label('Preview')
            ->state(fn (Media $r) => $r->isImage() ? url($r->url) : null)
            ->defaultImageUrl(fn (Media $r) => $r->isImage() ? null : 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 3"><rect width="4" height="3" fill="#e7f2f5"/></svg>'));
        // The stored name is the original filename without its extension; the extension comes from the saved path.
        $name = TextColumn::make('name')->label('File name')->searchable(['name', 'path', 'alt'])->sortable()->weight('medium')
            ->formatStateUsing(fn (Media $r) => $r->name.'.'.pathinfo($r->path, PATHINFO_EXTENSION))->limit(34)->tooltip(fn (Media $r) => $r->path);
        $meta = TextColumn::make('size')->label('Size')->sortable()->color('gray')->size('sm')
            ->formatStateUsing(fn (Media $r) => trim(($r->width ? "{$r->width}×{$r->height} · " : '').Number::fileSize($r->size)));
        $type = TextColumn::make('type')->label('Type')->badge()->color(fn (Media $r) => str_starts_with((string) $r->mime, 'image/') ? 'info' : (str_starts_with((string) $r->mime, 'video/') ? 'warning' : 'gray'))
            ->state(fn (Media $r) => strtoupper(pathinfo($r->path, PATHINFO_EXTENSION)));
        $added = TextColumn::make('created_at')->label('Date added')->date('d M Y')->sortable();

        return $table
            ->columns($grid
                ? [Stack::make([
                    $thumb->imageHeight(150)->extraImgAttributes(['style' => 'width:100%;max-width:100%;height:150px;object-fit:contain;border-radius:8px;background:#eef5f9'])->width('100%'),
                    $name,
                    Split::make([$meta, $type->grow(false)]),
                ])->space(2)]
                : [$thumb->imageHeight(48), $name, TextColumn::make('url')->label('URL')->copyable()->color('gray')->limit(50), $type, $meta, $added])
            ->contentGrid($grid ? ['md' => 3, 'xl' => 4, '2xl' => 5] : null)
            ->defaultSort('created_at', 'desc')
            ->paginated([24, 48, 96])
            ->recordActions([
                static::copyUrl(),
                EditAction::make()->iconButton()->tooltip('Edit details'),
                static::delete()->iconButton()->tooltip('Delete'),
            ])
            ->toolbarActions([static::bulkDelete()]);
    }

    /** Copies the file's full URL in the browser; nothing goes to the server. */
    public static function copyUrl(): Action
    {
        return Action::make('copyUrl')->label('Copy URL')->icon('heroicon-o-clipboard-document')->color('gray')->iconButton()->tooltip('Copy URL')
            ->alpineClickHandler(fn (Media $record) => 'window.navigator.clipboard.writeText('.Js::from(url($record->url)).'); $tooltip(\'URL copied\', { theme: $store.theme, timeout: 1500 })');
    }

    /** Bulk delete with the same guard: files in use are skipped and listed, the rest are removed. */
    public static function bulkDelete(): BulkAction
    {
        return BulkAction::make('delete')->label('Delete selected')->icon('heroicon-o-trash')->color('danger')
            ->requiresConfirmation()->modalDescription('Files the site still uses are skipped.')
            ->authorize(fn () => MediaResource::can('deleteAny'))
            ->action(function (Collection $records) {
                [$used, $free] = $records->partition(fn (Media $m) => $m->usages());
                $free->each->delete();
                $note = Notification::make()->title("Deleted {$free->count()} file(s)");
                $used->isEmpty()
                    ? $note->success()->send()
                    : $note->warning()->body('Kept because they are in use: '.$used->pluck('name')->take(10)->implode(', ').($used->count() > 10 ? '…' : ''))->persistent()->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /** Delete that refuses files the live site still uses (see Media::usages). */
    public static function delete(): DeleteAction
    {
        return DeleteAction::make()->before(function (DeleteAction $action, Media $record) {
            if ($uses = $record->usages()) {
                Notification::make()->danger()->title('This file is in use')->body('Used by: '.implode(', ', $uses).'. Remove it there first.')->send();
                $action->cancel();
            }
        });
    }
}
