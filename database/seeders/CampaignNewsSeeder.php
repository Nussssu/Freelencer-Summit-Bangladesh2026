<?php

namespace Database\Seeders;

use App\Enums\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Database\Seeder;

/**
 * The campaign's opening updates, written from the client brief (§1–4): what the summit is, its strands and how to
 * take part. No invented people, numbers or dates; each post is dated the day it is seeded. Idempotent (by slug), and
 * the organiser edits or unpublishes them in the CMS like any other post.
 */
class CampaignNewsSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'freelancer-summit-bangladesh-2026-goes-national',
                'category' => NewsCategory::Press,
                'title' => 'Freelancer Summit Bangladesh 2026 goes national',
                'excerpt' => 'Organised by BACCO with DoICT, the campaign builds momentum across the country and culminates in the Grand Summit in Dhaka.',
                'cover_image' => '/media/photos/gallery-10.jpg',
                'body' => <<<'HTML'
                    <p>Freelancer Summit Bangladesh 2026 is envisioned as the largest national platform dedicated to freelancers, aspiring freelancers, digital professionals, AI practitioners, entrepreneurs, students, marketplaces, technology companies and the wider digital economy ecosystem of Bangladesh.</p>
                    <p>The initiative is not a single-day event. It operates as a national campaign that builds momentum through activities across Bangladesh and culminates in the Grand Freelancer Summit in Dhaka in late November 2026.</p>
                    <h3>Organised by BACCO with DoICT</h3>
                    <p>The summit is jointly organised by the Bangladesh Association of Contact Center &amp; Outsourcing (BACCO) and the Department of Information and Communication Technology (DoICT), Government of Bangladesh.</p>
                    <h3>What the campaign sets out to do</h3>
                    <p>Its aim is to establish freelancing as one of the most respected and highly desired professions in Bangladesh, to connect university and polytechnic students with the global digital economy, and to build a national ecosystem for new freelancers, skilled freelancers and freelancer-to-entrepreneur journeys.</p>
                    HTML,
            ],
            [
                'slug' => 'campus-programs-bring-freelancing-to-universities',
                'category' => NewsCategory::Roadshow,
                'title' => 'Campus programs bring freelancing to universities',
                'excerpt' => 'Seminars, workshops, career talks, AI awareness and mentor sessions on university and polytechnic campuses.',
                'cover_image' => '/media/photos/gallery-02.jpg',
                'body' => <<<'HTML'
                    <p>Campus programs take Freelancer Summit Bangladesh to universities and polytechnics, so students can explore freelancing and the global digital economy where they already study.</p>
                    <h3>What happens on campus</h3>
                    <p>Each program combines seminars, hands-on workshops, career talks, AI awareness sessions and mentor sessions with practising freelancers.</p>
                    <p>The goal is to increase awareness among young people about freelancing, AI and digital service sectors, and to grow interest in ITES, BPO and AI-based digital services as a career.</p>
                    <p>Dates and host institutions are published on the campus programs page as each program is confirmed.</p>
                    HTML,
            ],
            [
                'slug' => 'district-roadshows-take-the-summit-to-every-region',
                'category' => NewsCategory::Roadshow,
                'title' => 'District roadshows take the summit to every region',
                'excerpt' => 'Freelancer roadshows, career awareness and skill development sessions for new and aspiring freelancers.',
                'cover_image' => '/media/photos/gallery-13.jpg',
                'body' => <<<'HTML'
                    <p>District roadshows carry the campaign beyond the capital, bringing the summit to freelancers and aspiring freelancers across the country.</p>
                    <h3>What each roadshow offers</h3>
                    <p>Roadshows feature freelancer awareness sessions, career guidance, skill development and the new freelancer campaign, with success stories, an AI showcase and marketplace sessions.</p>
                    <p>Activity in the districts builds momentum through the year towards the Grand Freelancer Summit in Dhaka.</p>
                    <p>Upcoming stops are listed on the district roadshows page as they are scheduled.</p>
                    HTML,
            ],
            [
                'slug' => 'freelancer-awards-to-honour-the-countrys-best',
                'category' => NewsCategory::Announcement,
                'title' => 'Freelancer Awards to honour the country’s best',
                'excerpt' => 'The Grand Summit in Dhaka recognises freelancers who are raising the bar for Bangladesh’s digital workforce.',
                'cover_image' => '/media/photos/gallery-12.jpg',
                'body' => <<<'HTML'
                    <p>The Freelancer Awards are part of the Grand Freelancer Summit in Dhaka, celebrating freelancers whose work sets the standard for Bangladesh's digital workforce.</p>
                    <h3>Recognition on a national stage</h3>
                    <p>The awards put outstanding freelancers in front of industry leaders, marketplaces and the companies that hire them, alongside the summit's keynotes, seminars, workshops and exhibition.</p>
                    <p>Award categories, eligibility and how to nominate are explained on the awards page.</p>
                    HTML,
            ],
            [
                'slug' => 'bacco-freelancer-forum-opens-membership',
                'category' => NewsCategory::Announcement,
                'title' => 'BACCO Freelancer Forum opens membership',
                'excerpt' => 'Register for a Freelancer ID and Forum membership to connect with peers, mentors and new opportunities.',
                'cover_image' => '/media/photos/gallery-06.jpg',
                'body' => <<<'HTML'
                    <p>The BACCO Freelancer Forum brings freelancers together as a community, with a place to connect with peers, mentors and the wider industry.</p>
                    <h3>Freelancer ID and Forum membership</h3>
                    <p>Facilitating Freelancer ID and BACCO Freelancer Forum membership registration is one of the summit's strategic objectives. Freelancers can register online through the forum page.</p>
                    <p>Members stay close to the campaign's programs, events and opportunities throughout the year.</p>
                    HTML,
            ],
            [
                'slug' => 'exhibit-and-sponsor-at-the-grand-summit',
                'category' => NewsCategory::Announcement,
                'title' => 'Exhibit and sponsor at the Grand Summit',
                'excerpt' => 'Marketplaces, technology companies and service providers can apply for booths and sponsorship in Dhaka.',
                'cover_image' => '/media/photos/gallery-05.jpg',
                'body' => <<<'HTML'
                    <p>The Grand Freelancer Summit in Dhaka includes an exhibition where marketplaces, technology companies and service providers meet freelancers, students and businesses.</p>
                    <h3>Booths and sponsorship</h3>
                    <p>Organisations can apply for exhibition space online, and sponsorship packages are available for partners who want visibility across the national campaign and the summit itself.</p>
                    <p>Booths are allocated once the floor plan is final. Details are on the exhibition and sponsorship pages.</p>
                    HTML,
            ],
        ];

        // Newest first in the order above: each post is a minute older than the one before it, all on the seeding day.
        // A re-run refreshes the copy but keeps the original publish date.
        foreach ($posts as $i => $post) {
            $row = NewsPost::firstOrNew(['slug' => $post['slug']]);
            $row->fill([...$post, 'is_sample' => false]);
            $row->published_at ??= now()->subMinutes($i);
            $row->save();
        }
    }
}
