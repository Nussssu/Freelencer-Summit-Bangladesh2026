<?php

namespace App\Enums;

/** Summit registration interests. */
enum Interest: string
{
    use HasOptions;

    case Ai = 'ai';
    case Marketplaces = 'marketplaces';
    case Skills = 'skills';
    case Entrepreneurship = 'entrepreneurship';
    case Career = 'career';
    case Networking = 'networking';
    case Exhibition = 'exhibition';
    case Awards = 'awards';

    const LABELS = ['ai' => 'AI', 'skills' => 'Skills and training'];
}
