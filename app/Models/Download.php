<?php

namespace App\Models;

use App\Enums\DownloadKind;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['kind' => DownloadKind::class, 'visible' => 'boolean'];
    }
}
