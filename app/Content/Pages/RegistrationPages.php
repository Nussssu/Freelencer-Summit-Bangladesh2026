<?php

namespace App\Content\Pages;

use App\Content\SiteContent;
use App\Enums\AttendeeType;
use App\Enums\Interest;
use App\Http\Controllers\SubmissionController;
use Illuminate\Http\Request;

/** Brief §8 (summit registration) and the contact page. */
class RegistrationPages
{
    use FormOptions;

    public function register(): array
    {
        return [
            'districts' => $this->districts(),
            'attendeeTypes' => AttendeeType::options(),
            'interests' => Interest::options(),
            'consent' => $this->consent(),
            'aside' => [
                'points' => ['Free to attend', 'Reference number by email straight away', 'E-ticket with QR code once the venue is confirmed'],
                'others' => [
                    ['label' => 'Nominate for the awards', 'href' => '/awards/nominate'],
                    ['label' => 'Exhibit at the summit', 'href' => '/exhibition/apply'],
                    ['label' => 'Become a sponsor', 'href' => '/sponsorship-opportunity'],
                    ['label' => 'Join the BACCO Freelancer Forum', 'href' => '/forum/register'],
                ],
            ],
        ];
    }

    public function contact(Request $request, SiteContent $site): array
    {
        $topic = (string) $request->query('topic', 'general');

        return [
            'topics' => self::options(SubmissionController::TOPICS),
            'topic' => array_key_exists($topic, SubmissionController::TOPICS) ? $topic : 'general',
            'consent' => $this->consent(),
            'contacts' => array_map(fn ($c, $i) => [...$c, 'icon' => ['ticket', 'trophy', 'handshake', 'store', 'people', 'news'][$i] ?? 'mail', 'tone' => ['tone-teal', 'tone-sun', 'tone-mint', 'tone-violet', 'tone-blue', 'tone-rose'][$i] ?? 'tone-teal'], $site->contacts(), array_keys($site->contacts())),
            'organisers' => $site->organisers(),
            'contactsNote' => 'Direct email addresses for each team are published once confirmed. Until then, the form reaches the right person.',
        ];
    }
}
