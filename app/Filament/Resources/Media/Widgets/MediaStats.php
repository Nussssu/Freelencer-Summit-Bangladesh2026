<?php

namespace App\Filament\Resources\Media\Widgets;

use App\Models\Media;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class MediaStats extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $files = Media::query()->get(['id', 'path', 'mime', 'size']);
        $images = $files->filter(fn (Media $m) => str_starts_with((string) $m->mime, 'image/'))->count();
        $uploads = $files->filter(fn (Media $m) => str_starts_with($m->path, 'uploads/'));
        // Same check the delete guard uses, so "in use" here always matches what can't be deleted.
        $unusedUploads = $uploads->reject(fn (Media $m) => $m->usages())->count();

        return [
            Stat::make('Total files', number_format($files->count()))->icon('heroicon-o-folder')
                ->description($uploads->count().' uploaded in the CMS'),
            Stat::make('Storage used', Number::fileSize($files->sum('size'), precision: 1))->icon('heroicon-o-circle-stack')
                ->description('public/media'),
            Stat::make('Images', number_format($images))->icon('heroicon-o-photo')
                ->description(($files->count() - $images).' videos and documents'),
            Stat::make('In use', number_format($files->count() - $unusedUploads))->icon('heroicon-o-link')
                ->description($unusedUploads ? "{$unusedUploads} unused upload(s) can be deleted" : 'Every file is in use')
                ->color($unusedUploads ? 'warning' : 'success'),
        ];
    }
}
