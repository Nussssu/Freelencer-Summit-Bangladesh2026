<?php

namespace App\Enums;

/** Public programme status. */
enum ProgramStatus: string
{
    use HasOptions;

    case Upcoming = 'upcoming';
    case RegistrationOpen = 'registration-open';
    case Completed = 'completed';
    case Postponed = 'postponed';

    const LABELS = ['registration-open' => 'Registration open'];
}
