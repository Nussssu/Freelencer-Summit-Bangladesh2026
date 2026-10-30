<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Media Library entry: one file on the `media` disk (public/media), served at /media/{path}. */
class Media extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::disk('media')->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function scopeImages(Builder $q): Builder
    {
        return $q->where('mime', 'like', 'image/%');
    }

    /** Register a file already on the media disk (import and upload share this). */
    public static function register(string $path, ?string $name = null): self
    {
        $abs = Storage::disk('media')->path($path);
        $mime = mime_content_type($abs) ?: null;
        [$w, $h] = $mime && str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml' ? (@getimagesize($abs) ?: [null, null]) : [null, null];

        return static::updateOrCreate(['path' => $path], [
            'name' => $name ?? pathinfo($path, PATHINFO_FILENAME),
            'mime' => $mime,
            'size' => filesize($abs) ?: 0,
            'width' => $w,
            'height' => $h,
        ]);
    }

    /** Add a library entry for every file on the disk that has none yet (first import, or files copied in by hand). */
    public static function syncDisk(): int
    {
        $known = static::query()->pluck('path')->flip();
        $new = collect(Storage::disk('media')->allFiles())
            ->reject(fn ($p) => isset($known[$p]) || str_starts_with(basename($p), '.'))
            ->each(fn ($p) => static::register($p));

        return $new->count();
    }

    public static function upload(UploadedFile $file): self
    {
        $path = $file->store('uploads/'.now()->format('Y/m'), 'media');

        return static::register($path, pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
    }

    /**
     * Why this file can't be deleted yet. Built-in assets (anything outside uploads/) are referenced from code,
     * sometimes by assembled paths a text search can't find, so they stay protected.
     * shortcut: built-in assets are all protected; once page content moves into CMS blocks (phase 2), check real references instead.
     *
     * @return list<string>
     */
    public function usages(): array
    {
        $hits = str_starts_with($this->path, 'uploads/') ? [] : ['Built-in site asset'];
        if (str_contains(json_encode(Settings::all(), JSON_UNESCAPED_SLASHES), $this->url)) {
            $hits[] = 'Site settings';
        }
        if (DB::table('pages')->where('og_image', $this->url)->exists()) {
            $hits[] = 'Page SEO';
        }

        return $hits;
    }

    protected static function booted(): void
    {
        static::deleted(fn (self $m) => Storage::disk('media')->delete($m->path));
    }
}
