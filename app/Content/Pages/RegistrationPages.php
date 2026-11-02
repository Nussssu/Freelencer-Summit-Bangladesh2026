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
            // The registration mock-up's choices and wording; other enum values stay valid for the CMS and older records.
            'attendeeTypes' => self::options([
                AttendeeType::Freelancer->value => 'Freelancer', AttendeeType::Student->value => 'Student', AttendeeType::Professional->value => 'Professional',
                AttendeeType::Entrepreneur->value => 'Entrepreneur', AttendeeType::Other->value => 'Other',
            ]),
            'interests' => self::options([
                Interest::Marketplaces->value => 'Freelancing & Marketplace', Interest::Ai->value => 'AI & Future of Work', Interest::Skills->value => 'Skills & Career Development',
                Interest::Entrepreneurship->value => 'Business & Entrepreneurship', Interest::Remote->value => 'Remote Work & Global Opportunities',
                Interest::Networking->value => 'Networking & Collaboration', Interest::Other->value => 'Others',
            ]),
            'consent' => $this->consent(),
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
