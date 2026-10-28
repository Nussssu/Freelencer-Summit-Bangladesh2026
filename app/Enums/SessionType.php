<?php

namespace App\Enums;

/** The 11 session types of brief §14, verbatim. */
enum SessionType: string
{
    use HasOptions;

    case Keynote = 'keynote';
    case Seminar = 'seminar';
    case Workshop = 'workshop';
    case CareerTalk = 'career-talk';
    case Panel = 'panel';
    case AiAwareness = 'ai-awareness';
    case SuccessStory = 'success-story';
    case MarketplaceSession = 'marketplace-session';
    case FreelancerToEntrepreneur = 'freelancer-to-entrepreneur';
    case AwardSession = 'award-session';
    case Networking = 'networking';

    const LABELS = ['career-talk' => 'Career Talk', 'ai-awareness' => 'AI Awareness', 'success-story' => 'Success Story', 'marketplace-session' => 'Marketplace Session', 'freelancer-to-entrepreneur' => 'Freelancer-to-Entrepreneur', 'award-session' => 'Award Session', 'keynote' => 'Keynote', 'seminar' => 'Seminar', 'workshop' => 'Workshop', 'panel' => 'Panel', 'networking' => 'Networking'];
}
