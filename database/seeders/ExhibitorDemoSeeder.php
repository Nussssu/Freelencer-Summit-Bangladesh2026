<?php

namespace Database\Seeders;

use App\Models\Booth;
use App\Models\Exhibitor;
use Illuminate\Database\Seeder;

/**
 * Local preview exhibitors for the exhibitor list (DemoSeeder runs it, local only). Fictional companies with
 * believable names and profiles so the list and profile pages can be reviewed at full length; every row keeps
 * is_sample = true so they are easy to find and remove (Exhibitor::where('is_sample', true)->delete()).
 */
class ExhibitorDemoSeeder extends Seeder
{
    private const ROWS = [
        ['Ashwa Talent Exchange', 'Marketplace', 'A freelance marketplace built for Bangladeshi professionals, with escrow payments in BDT and a verified-skills badge that buyers can trust.'],
        ['Banyan Code Collective', 'Software', 'A remote-first software studio of 60 freelance engineers delivering web and mobile products for clients in Europe and the Gulf.'],
        ['Barind Skills Academy', 'Training', 'Hands-on bootcamps in graphic design, video editing and digital marketing, run out of Rajshahi with online cohorts nationwide.'],
        ['Bayside Outsourcing Ltd', 'BPO and ITES', 'Customer support and back-office operations for e-commerce brands, staffed by trained agents across Chattogram and Cox\'s Bazar.'],
        ['Bijoy Pixel Studio', 'Digital Agency', 'Branding, motion graphics and social content for start-ups, produced by a network of freelance designers.'],
        ['Brahmaputra Data Labs', 'Software', 'Data annotation, cleaning and dashboard work for AI teams, with secure project rooms and quality checks on every batch.'],
        ['Chalantika Pay', 'Fintech', 'Fast, low-fee international payouts for freelancers, with same-day settlement to local bank accounts and mobile wallets.'],
        ['Chandrabindu Learning', 'Training', 'Short courses that take students from their first gig to a steady client base, taught by top-rated freelancers.'],
        ['Dhanshiri Contact Centre', 'BPO and ITES', 'Multilingual voice and chat support around the clock, with English, Arabic and Bangla teams.'],
        ['Dighi Web Works', 'Digital Agency', 'WordPress and Shopify builds, speed tuning and maintenance plans for small businesses at home and abroad.'],
        ['Ekota Remote Hub', 'Marketplace', 'Co-working spaces and a job board in eight divisional cities, connecting freelancers with stable remote roles.'],
        ['Gaurab Cloud Services', 'Software', 'Cloud migration and DevOps support delivered by certified freelance engineers on flexible monthly plans.'],
        ['Hijol Creative Network', 'Digital Agency', 'A collective of illustrators, animators and copywriters producing campaign work for international agencies.'],
        ['Jamuna Freelance Guild', 'Marketplace', 'A members\' guild offering contract templates, rate guidance and dispute help for independent professionals.'],
        ['Kantha Commerce Studio', 'Digital Agency', 'Product photography, listing optimisation and store management for marketplace sellers.'],
        ['Karnaphuli Finance Desk', 'Fintech', 'Bookkeeping, invoicing and tax filing support designed around the way freelancers earn.'],
        ['Kopotakkho IT Institute', 'Training', 'Industry-certified courses in networking, cybersecurity and cloud, with job placement support for graduates.'],
        ['Lalbagh Language Services', 'BPO and ITES', 'Translation, transcription and localisation in more than 20 languages, delivered by vetted freelance linguists.'],
        ['Madhumati Media House', 'Digital Agency', 'Video production and post-production for YouTube channels, e-learning platforms and brands.'],
        ['Meghdut Mobile Labs', 'Software', 'Flutter and React Native app development, from prototype to store launch, for founders on a budget.'],
        ['Nakshi Wallet', 'Fintech', 'A digital wallet that receives marketplace earnings, converts at fair rates and pays bills in a few taps.'],
        ['Nilgiri UX Studio', 'Software', 'User research, interface design and usability testing for SaaS products, run by senior freelance designers.'],
        ['Padmapar Digital Skills', 'Training', 'Free and low-cost evening classes for youth in rural districts, with laptops on loan for top learners.'],
        ['Rangamati Remote Works', 'BPO and ITES', 'Data entry, research and virtual assistant services from a hill-district team trained to international standards.'],
        ['Rupsha Gig Network', 'Marketplace', 'A local gig app matching small businesses with nearby freelancers for design, writing and tech support.'],
        ['Sagorika Secure Pay', 'Fintech', 'Payment gateway and escrow for cross-border freelance contracts, with built-in currency hedging.'],
        ['Shalbon Analytics', 'Software', 'Marketing analytics, tracking setup and reporting for e-commerce brands, delivered by certified analysts.'],
        ['Shapla Content Studio', 'Digital Agency', 'SEO articles, product copy and newsletters written by a team of 40 freelance writers and editors.'],
        ['Shitalakshya BPO Services', 'BPO and ITES', 'Medical billing, insurance processing and claims support for clients in North America.'],
        ['Sonargaon Career Lab', 'Training', 'Career coaching, portfolio reviews and mock interviews for graduates moving into remote work.'],
        ['Sundarban Tech Solutions', 'Software', 'Custom ERP and inventory systems for factories and retailers, built and supported by local developers.'],
        ['Surma Payments', 'Fintech', 'Business accounts for agencies and teams, with multi-user approvals and automated payroll for freelancers.'],
        ['Tista Talent Bridge', 'Marketplace', 'A curated platform placing senior Bangladeshi freelancers on long-term contracts with overseas companies.'],
        ['Ujaan Game Studio', 'Software', 'Mobile game art and development, from 2D assets to full casual titles, for publishers worldwide.'],
        ['Varendra Design School', 'Training', 'A design school offering diploma courses in UI, branding and 3D, with studio projects for real clients.'],
        ['Zarif Virtual Assistants', 'BPO and ITES', 'Executive assistants, inbox and calendar management for founders and consultants across time zones.'],
    ];

    public function run(): void
    {
        // Replace the generic "Sample Exhibitor nn" rows from the factory
        Exhibitor::where('slug', 'like', 'sample-exhibitor-%')->delete();

        $booths = Booth::orderBy('code')->pluck('id')->all();
        $next = 0;
        foreach (self::ROWS as $i => [$name, $sector, $description]) {
            $slug = str($name)->slug()->toString();
            Exhibitor::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'sector' => $sector,
                'description' => $description,
                'website' => "https://{$slug}.example",
                // Every third exhibitor is still waiting for a stand
                'booth_id' => $i % 3 === 2 ? null : ($booths[$next++] ?? null),
                'visible' => true,
                'is_sample' => true,
            ]);
        }
    }
}
