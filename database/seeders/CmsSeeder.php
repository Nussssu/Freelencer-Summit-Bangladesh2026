<?php

namespace Database\Seeders;

use App\Content\PageContent;
use App\Models\EmailTemplate;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Database\Seeder;

/** CMS starting state: library entries for public/media, one SEO row per routed page, the two lead emails. Safe to re-run. */
class CmsSeeder extends Seeder
{
    public function run(): void
    {
        Media::syncDisk();

        // SEO fields start blank: blank means "use the coded title and description".
        foreach (['/' => ['Home'], ...PageContent::PAGES] as $slug => [$title]) {
            Page::firstOrCreate(['slug' => $slug], ['title' => $title, 'is_draft' => false]);
        }

        EmailTemplate::firstOrCreate(['key' => 'admin_notification'], [
            'name' => 'Admin notification',
            'subject' => 'New {type}: {name} ({reference})',
            'heading' => 'New {type}',
            'body' => "A new submission arrived on the website.\n\n**Reference:** {reference}\n\n{fields}\n\nReply to {email} or update its status in the CMS under Leads.",
        ]);
        EmailTemplate::firstOrCreate(['key' => 'user_confirmation'], [
            'name' => 'User confirmation',
            'subject' => 'We received your {type} · {reference}',
            'heading' => 'Thank you, {name}',
            'body' => "We have received your {type} for {site}.\n\nYour reference number is **{reference}**. Keep it for any follow-up with the team.\n\nWe will be in touch by email soon.",
        ]);
    }
}
