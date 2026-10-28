<?php

namespace App\Enums;

/** Summit registration attendee types. */
enum AttendeeType: string
{
    use HasOptions;

    case Freelancer = 'freelancer';
    case AspiringFreelancer = 'aspiring-freelancer';
    case Student = 'student';
    case Entrepreneur = 'entrepreneur';
    case Company = 'company';
    case Marketplace = 'marketplace';
    case Investor = 'investor';
    case Partner = 'partner';
    case Government = 'government';
    case Media = 'media';
    case Other = 'other';

    const LABELS = ['aspiring-freelancer' => 'Aspiring freelancer', 'company' => 'Company or buyer', 'government' => 'Government or policy'];
}
