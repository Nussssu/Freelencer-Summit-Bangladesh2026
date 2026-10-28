<?php

namespace App\Enums;

/** Sponsorship enquiry pipeline. */
enum SponsorshipStatus: string
{
    use HasOptions;

    case New = 'new';
    case Contacted = 'contacted';
    case Negotiation = 'negotiation';
    case Confirmed = 'confirmed';
    case Closed = 'closed';
}
