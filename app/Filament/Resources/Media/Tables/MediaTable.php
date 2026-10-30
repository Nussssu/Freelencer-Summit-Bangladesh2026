<?php

namespace App\Filament\Resources\Media\Tables;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        $grid = ($table->getLivewire()->display ?? 'grid') === 'grid';
        $thumb = ImageColumn::make('preview')->label('Preview')
            ->state(fn (Media $r) => $r->isImage() ? url($r->url) : null)
            ->defaultImageUrl(fn (Media $r) => $r->isImage() ? null : 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 3"><rect width="4" height="3" fill="#e7f2f5"/></svg>'));
        $name = TextColumn::make('name')->searchable(['name', 'path', 'alt'])->sortable()->weight('medium')->limit(40);
        $meta = TextColumn::make('size')->formatStateUsing(fn (Media $r) => trim(($r->width ? "{$r->width}×{$r->height} · " : '').Number::fileSize($r->size)))->color('gray')->size('sm');

        return $table
            ->columns($grid
                ? [Stack::make([$thumb->imageHeight(150)->extraImgAttributes(['style' => 'width:100%;max-width:100%;height:150px;object-fit:contain;border-radius:8px;background:#f5f2e9'])->width('100%'), $name, $meta])->space(2)]
                : [$thumb->imageHeight(48), $name, TextColumn::make('url')->label('URL')->copyable()->color('gray')->limit(50), TextColumn::make('mime')->label('Type')->badge(), $meta->label('Size'), TextColumn::make('created_at')->label('Added')->date()->sortable()])
            ->contentGrid($grid ? ['md' => 3, 'xl' => 5] : null)
            ->defaultSort('created_at', 'desc')
            ->paginated([20, 40, 80])
            ->filters([
                SelectFilter::make('kind')->label('Type')
                    ->options(['image' => 'Images', 'video' => 'Video', 'other' => 'Other'])
                    ->query(fn ($q, array $data) => match ($data['value'] ?? null) {
                        'image' => $q->where('mime', 'like', 'image/%'),
                        'video' => $q->where('mime', 'like', 'video/%'),
                        'other' => $q->where('mime', 'not like', 'image/%')->where('mime', 'not like', 'video/%'),
                        default => $q,
                    }),
                SelectFilter::make('source')->options(['uploads' => 'Uploaded in CMS', 'site' => 'Built-in site assets'])
                    ->query(fn ($q, array $data) => match ($data['value'] ?? null) {
                        'uploads' => $q->where('path', 'like', 'uploads/%'),
                        'site' => $q->where('path', 'not like', 'uploads/%'),
                        default => $q,
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                static::delete(),
            ])
            ->toolbarActions([static::bulkDelete()]);
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
