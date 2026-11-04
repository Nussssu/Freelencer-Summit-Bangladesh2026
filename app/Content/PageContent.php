<?php

namespace App\Content;

/** Meta and intro copy for pages that are routed but not built yet ("Coming next"). */
class PageContent
{
    /** path => [title, description, what the page will hold] */
    public const PAGES = [
        '/about' => ['About Summit', 'Why Freelancer Summit Bangladesh exists, who organises it and how the national campaign works.', 'The summit story, organisers and objectives'],
        '/national-journey' => ['National Journey', 'Campus programmes, district roadshows and divisional summits leading to the Grand Summit in Dhaka.', 'The full 8-division journey map and timeline'],
        '/divisional-summits' => ['Divisional Summits', 'One summit in each of the 8 divisions of Bangladesh.', 'A page for each divisional summit with dates, venues and speakers'],
        '/district-roadshows' => ['District Summits', 'Freelancer roadshows across the districts of Bangladesh.', 'District roadshow pages, schedules and registration'],
        '/campus-programs' => ['Campus Programs', 'Seminars, workshops and career talks at universities and polytechnics.', 'A searchable directory of campus activations'],
        '/divisional-summits/{slug}' => ['Divisional summit', 'A divisional summit of Freelancer Summit Bangladesh.', 'The divisional summit page'],
        '/district-roadshows/{slug}' => ['District roadshow', 'A district roadshow of Freelancer Summit Bangladesh.', 'The district roadshow page'],
        '/campus-programs/{slug}' => ['Campus program', 'A campus program of Freelancer Summit Bangladesh.', 'The campus program page'],
        '/exhibition/exhibitors' => ['Exhibitors', 'Every exhibitor at Freelancer Summit Bangladesh, with their stand number.', 'The full exhibitor list'],
        '/exhibition/exhibitors/{slug}' => ['Exhibitor', 'An exhibitor at Freelancer Summit Bangladesh.', 'The exhibitor profile'],
        '/grand-summit' => ['Grand Summit', 'The Grand Freelancer Summit in Dhaka, late November.', 'Date, venue, map, countdown and the full event guide'],
        '/agenda' => ['Agenda', 'Sessions, halls and speakers for the Grand Summit.', 'A filterable agenda by day, session type and hall'],
        '/agenda/session/{slug}' => ['Session details', 'A session at the Grand Summit of Freelancer Summit Bangladesh.', 'The full session page'],
        '/speakers' => ['Speakers & Experts', 'Speakers, trainers and marketplace representatives at the summit.', 'The speaker directory with filters by country and expertise'],
        '/speaker/{slug}' => ['Speaker profile', 'Speaker biography, expertise and sessions.', 'Full speaker profile with biography and sessions'],
        '/awards' => ['Freelancer Awards', 'National awards for Bangladeshi freelancers.', 'Award overview, categories, criteria and winners archive'],
        '/awards/winners' => ['Freelancer Award Winners', 'Freelancer Awards winners and the permanent winners archive, by year.', 'Every winner, listed by year with the award category'],
        '/awards/nominees' => ['Shortlisted Nominees', 'Shortlisted nominees for the Freelancer Awards, by category.', 'Every shortlisted nominee, listed by award category'],
        '/awards/nominate' => ['Nominate for Awards', 'Submit a nomination or application for the Freelancer Awards.', 'The award nomination form with portfolio and declaration'],
        '/sponsors' => ['Sponsors', 'Organisations sponsoring Freelancer Summit Bangladesh.', 'Confirmed sponsors by tier'],
        '/sponsorship-opportunity' => ['Become a Sponsor', 'Sponsorship tiers, benefits and the sponsor enquiry form.', 'Sponsorship packages, prospectus and enquiry form'],
        '/exhibition' => ['Exhibition', 'Who should exhibit at the summit and why.', 'Exhibition guide, floor plan and exhibitor directory'],
        '/exhibition/apply' => ['Exhibit at Summit', 'Apply for an exhibition booth.', 'The booth application form'],
        '/forum' => ['BACCO Freelancer Forum', 'A long-term professional community for Bangladeshi freelancers.', 'Forum purpose, benefits, eligibility and FAQ'],
        '/forum/register' => ['Join BACCO Freelancer Forum', 'Apply for BACCO Freelancer Forum membership.', 'The Forum membership application'],
        '/register' => ['Register for Summit', 'Register to attend the Grand Summit in Dhaka.', 'Summit registration with confirmation and e-ticket'],
        '/success-stories' => ['Success Stories', 'Bangladeshi freelancers and entrepreneurs in their own words.', 'Profiles and videos'],
        '/news' => ['News & Updates', 'Announcements, roadshow updates and media coverage.', 'News, press releases and coverage'],
        '/news/{slug}' => ['News article', 'A Freelancer Summit Bangladesh news article.', 'The full article'],
        '/media' => ['Media Gallery', 'Photos and videos from divisional, district and campus programmes.', 'Photo and video galleries by programme'],
        '/partners' => ['Partners', 'Government, development, strategic and supporting partners.', 'All partners by category'],
        '/contact' => ['Contact', 'Contact the Freelancer Summit Bangladesh team.', 'Contacts for each journey and the organisers'],
        '/privacy-policy' => ['Privacy Policy', 'How Freelancer Summit Bangladesh handles personal data.', 'The privacy policy (awaiting the organiser\'s approved text)'],
        '/terms' => ['Terms', 'Terms of use for this website and its registrations.', 'Terms of use (awaiting the organiser\'s approved text)'],
    ];

    public static function for(string $pattern): array
    {
        [$title, $description, $upcoming] = self::PAGES[$pattern];

        return compact('title', 'description', 'upcoming');
    }
}
