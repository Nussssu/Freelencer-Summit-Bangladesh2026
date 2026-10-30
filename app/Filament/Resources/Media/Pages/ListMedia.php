<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    #[Url]
    public string $display = 'grid';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('layout')
                ->label($this->display === 'grid' ? 'List view' : 'Grid view')
                ->icon($this->display === 'grid' ? Heroicon::OutlinedListBullet : Heroicon::OutlinedSquares2x2)
                ->color('gray')
                ->action(function () {
                    $this->display = $this->display === 'grid' ? 'list' : 'grid';
                    $this->resetTable();
                }),
            Action::make('scan')
                ->label('Scan media folder')
                ->tooltip('Add files copied into public/media by hand')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->authorize(fn () => MediaResource::can('create'))
                ->action(fn () => Notification::make()->success()->title(Media::syncDisk().' new file(s) added')->send()),
            Action::make('upload')
                ->label('Upload files')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->authorize(fn () => MediaResource::can('create'))
                ->modalWidth('2xl')
                ->schema([
                    // Drag-and-drop zone; files go to public/media/uploads/{year}/{month}.
                    FileUpload::make('files')->hiddenLabel()->multiple()->maxFiles(30)->maxSize(20 * 1024)
                        ->disk('media')->directory(fn () => 'uploads/'.now()->format('Y/m'))->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'video/mp4', 'video/webm', 'application/pdf'])
                        ->storeFileNamesIn('names')->panelLayout('grid')->required(),
                ])
                ->action(function (array $data) {
                    foreach ((array) $data['files'] as $path) {
                        Media::register($path, pathinfo($data['names'][$path] ?? $path, PATHINFO_FILENAME));
                    }
                    Notification::make()->success()->title(count((array) $data['files']).' file(s) uploaded')->send();
                }),
        ];
    }
}
