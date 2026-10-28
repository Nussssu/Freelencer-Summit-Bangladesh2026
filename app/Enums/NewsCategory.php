<?php

namespace App\Enums;

/** News tabs. */
enum NewsCategory: string
{
    use HasOptions;

    case Announcement = 'announcement';
    case Roadshow = 'roadshow';
    case Speakers = 'speakers';
    case Press = 'press';
    case InTheMedia = 'in-the-media';

    const LABELS = ['roadshow' => 'Roadshow updates', 'speakers' => 'Speaker announcements', 'press' => 'Press releases', 'in-the-media' => 'In the media'];
}
