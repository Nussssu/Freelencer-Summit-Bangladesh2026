<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/** Private document uploads: PDF/JPG/PNG, size-limited, random names on the private disk. */
class Uploads
{
    public const MAX_KB = 5120;

    public const MAX_FILES = 5;

    public static function rules(): array
    {
        return ['file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:'.self::MAX_KB];
    }

    /** @return array{path: string, original_name: string, mime: string, size: int} */
    public static function store(UploadedFile $file, string $dir): array
    {
        return [
            'path' => $file->store($dir, 'private'), // hashName(): random, keeps a safe extension
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }
}
