<?php

namespace App\Enums;

/** Floor-plan legend. */
enum BoothStatus: string
{
    use HasOptions;

    case Available = 'available';
    case Reserved = 'reserved';
    case Confirmed = 'confirmed';
}
