<?php

namespace App\Support;

/**
 * Admin workflow statuses for the public submissions (value => label) and their badge colours. One place so the
 * tables, filters, bulk actions and exports of every module agree.
 */
class Statuses
{
    /** Grand Summit and programme registrations. */
    public const REGISTRATION = ['registered' => 'Registered', 'confirmed' => 'Confirmed', 'attended' => 'Attended', 'cancelled' => 'Cancelled'];

    /** BACCO Freelancer Forum memberships and Campus Ambassador applications. */
    public const APPLICATION = ['pending' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected'];

    /** Exhibition booth applications and exhibitor enquiries. */
    public const ENQUIRY = ['new' => 'New', 'contacted' => 'Contacted', 'confirmed' => 'Confirmed', 'closed' => 'Closed'];

    /** Contact messages. */
    public const MESSAGE = ['new' => 'New', 'replied' => 'Replied', 'closed' => 'Closed'];

    public const COLORS = [
        'registered' => 'gray', 'confirmed' => 'success', 'attended' => 'info', 'cancelled' => 'danger',
        'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger',
        'new' => 'warning', 'contacted' => 'info', 'negotiation' => 'primary', 'closed' => 'gray', 'replied' => 'success',
        'submitted' => 'gray', 'under-review' => 'warning', 'shortlisted' => 'info', 'winner' => 'success',
        'sent' => 'success', 'failed' => 'danger',
    ];

    public static function color(?string $status): string
    {
        return self::COLORS[$status] ?? 'gray';
    }
}
