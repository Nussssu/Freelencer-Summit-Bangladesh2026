<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Media\Widgets\MediaStats;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    /** What the dropzone accepts; shown to the uploader and enforced by the field. */
    private const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'video/mp4', 'video/webm', 'application/pdf'];

    private const MAX_MB = 20;

    #[Url]
    public string $display = 'grid';

    public ?array $upload = [];

    public function mount(): void
    {
        parent::mount();
        $this->uploadForm->fill();
    }

    protected function getHeaderWidgets(): array
    {
        return [MediaStats::class];
    }

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
        ];
    }

    /** Quick filters above the grid, each with its file count. */
    public function getTabs(): array
    {
        $tab = fn (string $label, ?\Closure $scope) => Tab::make($label)
            ->badge(fn () => $scope ? $scope(Media::query())->count() : Media::query()->count())
            ->modifyQueryUsing(fn (Builder $query) => $scope ? $scope($query) : $query);

        return [
            'all' => $tab('All files', null),
            'images' => $tab('Images', fn ($query) => $query->where('mime', 'like', 'image/%')),
            'videos' => $tab('Videos', fn ($query) => $query->where('mime', 'like', 'video/%')),
            'documents' => $tab('Documents', fn ($query) => $query->where('mime', 'not like', 'image/%')->where('mime', 'not like', 'video/%')),
            'uploads' => $tab('Uploaded in CMS', fn ($query) => $query->where('path', 'like', 'uploads/%')),
            'site' => $tab('Built-in', fn ($query) => $query->where('path', 'not like', 'uploads/%')),
        ];
    }

    public function uploadForm(Schema $schema): Schema
    {
        return $schema->statePath('upload')->components([
            // Files go to public/media/uploads/{year}/{month}, so their URLs are ordinary /media/... paths.
            FileUpload::make('files')->hiddenLabel()->multiple()->maxFiles(30)->maxSize(self::MAX_MB * 1024)
                ->disk('media')->directory(fn () => 'uploads/'.now()->format('Y/m'))->visibility('public')
                ->acceptedFileTypes(self::ACCEPTED)->storeFileNamesIn('names')->panelLayout('grid')->required()
                ->placeholder('Drag & drop files here or <span class="filepond--label-action">browse</span>'),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Upload files')
                ->description('JPG, PNG, WebP, GIF, SVG, MP4, WebM or PDF · up to '.self::MAX_MB.' MB each · 30 files at a time')
                ->icon(Heroicon::OutlinedCloudArrowUp)->collapsible()
                ->visible(fn () => MediaResource::can('create'))
                ->schema([
                    Form::make([EmbeddedSchema::make('uploadForm')])->livewireSubmitHandler('saveUploads')->footer([
                        Actions::make([Action::make('saveUploads')->label('Add to library')->icon(Heroicon::OutlinedArrowUpTray)->submit('saveUploads')]),
                    ]),
                ]),
            ...parent::content($schema)->getComponents(),
        ]);
    }

    public function saveUploads(): void
    {
        abort_unless(MediaResource::can('create'), 403);
        $data = $this->uploadForm->getState();

        foreach ((array) $data['files'] as $path) {
            Media::register($path, pathinfo($data['names'][$path] ?? $path, PATHINFO_FILENAME));
        }
        $this->uploadForm->fill();
        $this->resetTable();
        $this->dispatch('$refresh')->to(MediaStats::class);
        Notification::make()->success()->title(count((array) $data['files']).' file(s) added to the library')->send();
    }
}
