<?php

namespace App\Enums;

/** Award application status (brief §9). Rejected is never shown publicly. */
enum AwardStatus: string
{
    use HasOptions;

    case Submitted = 'submitted';
    case UnderReview = 'under-review';
    case Shortlisted = 'shortlisted';
    case Winner = 'winner';
    case Rejected = 'rejected';

    const LABELS = ['under-review' => 'Under Review', 'rejected' => 'Rejected / Incomplete', 'submitted' => 'Submitted', 'shortlisted' => 'Shortlisted', 'winner' => 'Winner'];
}
