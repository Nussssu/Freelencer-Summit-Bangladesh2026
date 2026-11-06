<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessStory extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['published' => 'boolean', 'is_sample' => 'boolean'];
    }

    /** The 11-character video id from a YouTube link (watch, youtu.be, shorts, embed) or a bare id; null otherwise. */
    public static function youtubeId(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value)) {
            return $value;
        }
        preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $value, $m);

        return $m[1] ?? null;
    }

    public function youtubeUrl(): ?string
    {
        return $this->youtube_id ? "https://www.youtube.com/watch?v={$this->youtube_id}" : null;
    }
}
