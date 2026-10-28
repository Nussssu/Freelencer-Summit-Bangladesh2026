<?php

namespace App\Enums;

/** Partner groups on /partners. */
enum PartnerCategory: string
{
    use HasOptions;

    case Organiser = 'organiser';
    case Government = 'government';
    case Strategic = 'strategic';
    case Supporting = 'supporting';

    const LABELS = ['government' => 'Government and development'];
}
