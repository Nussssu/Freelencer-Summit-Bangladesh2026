<?php

namespace App\Enums;

/** Downloadable files; a card is enabled only when a file exists. */
enum DownloadKind: string
{
    use HasOptions;

    case Brochure = 'brochure';
    case Agenda = 'agenda';
    case Prospectus = 'prospectus';
    case Guidelines = 'guidelines';
    case MediaKit = 'media-kit';

    const LABELS = ['prospectus' => 'Sponsorship prospectus', 'guidelines' => 'Award guidelines', 'media-kit' => 'Media kit'];
}
