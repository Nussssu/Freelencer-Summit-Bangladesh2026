<?php

namespace Database\Seeders;

use App\Content\PageContent;
use App\Models\EmailTemplate;
use App\Models\Media;
use App\Models\Page;
use App\Support\Access;
use App\Support\PageBlocks;
use Illuminate\Database\Seeder;

/** CMS starting state: library entries for public/media, one SEO row per routed page, the lead emails, roles and permissions. Safe to re-run. */
class CmsSeeder extends Seeder
{
    public function run(): void
    {
        Media::syncDisk();
        Access::sync();

        // SEO fields start blank (blank means "use the coded title and description"); content blocks start as the coded content.
        foreach (['/' => ['Home'], ...PageContent::PAGES] as $slug => [$title]) {
            // A page deleted in the CMS stays offline: re-seeding neither restores nor duplicates it.
            $page = Page::withTrashed()->firstOrCreate(['slug' => $slug], ['title' => $title, 'is_draft' => false]);
            if (! $page->sections && ($blocks = PageBlocks::seed($slug))) {
                $page->forceFill(['sections' => $blocks])->saveQuietly();
            }
        }

        // Phase 1 names → the names staff use; only renamed while still at the old default.
        EmailTemplate::where('key', 'admin_notification')->where('name', 'Admin notification')->update(['name' => 'New lead alert']);
        EmailTemplate::where('key', 'user_confirmation')->where('name', 'User confirmation')->update(['name' => 'Enquiry received']);

        EmailTemplate::firstOrCreate(['key' => 'admin_notification'], [
            'name' => 'New lead alert',
            'subject' => 'New {type}: {name} ({reference})',
            'heading' => 'New {type}',
            'body' => "A new submission arrived on the website.\n\n**Reference:** {reference}\n\n{fields}\n\nReply to {email} or update its status in the CMS under Leads.",
        ]);
        EmailTemplate::firstOrCreate(['key' => 'registration_confirmed'], [
            'name' => 'Registration confirmed',
            'subject' => EmailTemplate::REGISTRATION_SUBJECT,
            'heading' => EmailTemplate::REGISTRATION_HEADING,
            'body' => EmailTemplate::REGISTRATION_BODY,
        ]);
        EmailTemplate::firstOrCreate(['key' => 'forum_approved'], EmailTemplate::FORUM_APPROVED);
        EmailTemplate::firstOrCreate(['key' => 'user_confirmation'], [
            'name' => 'Enquiry received',
            'subject' => 'We received your {type} · {reference}',
            'heading' => 'Thank you, {name}',
            'body' => "We have received your {type} for {site}.\n\nYour reference number is **{reference}**. Keep it for any follow-up with the team.\n\nWe will be in touch by email soon.",
        ]);
    }
}
