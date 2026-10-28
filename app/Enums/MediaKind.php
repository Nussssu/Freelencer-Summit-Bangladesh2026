<?php

namespace App\Enums;

enum MediaKind: string
{
    use HasOptions;

    case Photo = 'photo';
    case Video = 'video';
}
