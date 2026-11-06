<?php

namespace App\Support;

/**
 * Placeholder text (the grey hint inside an empty input) for every public form, keyed by the field's `name`. A key is
 * shared by every form that has that field (e.g. "email"), so one edit in Settings › Form placeholders updates them all.
 * Saved values (site_settings "form_placeholders") are laid over the coded defaults; a blank one keeps the default.
 */
class FormPlaceholders
{
    /** Admin group => [field name => [admin label, default placeholder]] */
    public const GROUPS = [
        'Shared fields (every form)' => [
            'name' => ['Full name', 'Enter your full name'],
            'email' => ['Email address', 'name@example.com'],
            'mobile' => ['Mobile number (after +880)', '1XXXXXXXXX'],
            'designation' => ['Designation', 'e.g. Marketing Manager'],
            'organisation' => ['Organisation / institution', 'Enter your organisation or institution'],
            'institution' => ['Institution (programme forms)', 'e.g. University of Dhaka'],
            'district' => ['City (dropdown)', 'Select your city'],
            'contact_person' => ['Contact person', 'Enter the contact person’s full name'],
            'message' => ['Message', 'Write your message or any additional details'],
            'requirements' => ['Other requirements', 'Tell us about any specific requirements'],
            'link' => ['Link rows (portfolio / profiles)', 'https://'],
            'platform' => ['Platform (link dropdown)', 'Select platform'],
        ],
        'Contact form' => [
            'topic' => ['Topic (dropdown)', 'Select a topic'],
        ],
        'Exhibition & sponsorship enquiry (home page)' => [
            'company_name' => ['Company name', 'Enter your company name'],
            'website' => ['Website URL', 'https://www.example.com'],
            'industry' => ['Industry / sector', 'e.g. IT services, BPO, e-commerce'],
            'head_office' => ['Head office location', 'e.g. Dhaka'],
            'full_name' => ['Full name', 'Enter your full name'],
            'phone' => ['Phone / WhatsApp', '+880 1XXXXXXXXX'],
        ],
        'Exhibit at Summit' => [
            'sector' => ['Industry or sector (dropdown)', 'Select your industry or sector'],
            'products' => ['Products or services', 'Describe the products or services you will showcase'],
            'branding_needs' => ['Branding needs', 'Banners, backdrops, logo placement or other branding needs'],
        ],
        'Become a Sponsor' => [
            'company' => ['Company / organisation name', 'Enter your company or organisation name'],
            'interest' => ['Sponsorship package (dropdown)', 'Select sponsorship package / area'],
            'budget' => ['Budget range (dropdown)', 'Select your budget range'],
        ],
        'BACCO Freelancer Forum' => [
            'nid' => ['NID number', 'Enter your 10, 13 or 17 digit NID number'],
            'services' => ['Services you offer', 'e.g. Web development, logo design, SEO, video editing'],
            'availability' => ['Availability (dropdown)', 'Select your availability'],
        ],
        'Nominate for Awards' => [
            'nominee_name' => ['Nominee full name', 'Enter the nominee’s full name'],
            'nominee_email' => ['Nominee email', 'nominee@example.com'],
            'nominee_mobile' => ['Nominee mobile (after +880)', '1XXXXXXXXX'],
            'headline' => ['Professional title / headline', 'e.g. Full-stack Developer on Upwork'],
            'experience' => ['Years of experience (dropdown)', 'Select experience'],
            'bio' => ['Short bio', 'Write a brief introduction about yourself (max 300 characters)'],
            'achievements' => ['Achievements', 'Share clients served, results delivered, growth, awards and community work'],
            'portfolio' => ['Portfolio links', 'https://yourportfolio.com'],
            'profiles' => ['Marketplace profiles', 'https://www.upwork.com/freelancers/your-profile'],
            'nominator_name' => ['Your name (nominator)', 'Enter your full name'],
            'nominator_email' => ['Your email (nominator)', 'name@example.com'],
            'nominator_mobile' => ['Your mobile (nominator, after +880)', '1XXXXXXXXX'],
        ],
        'Campus ambassador' => [
            'department' => ['Department', 'e.g. Computer Science and Engineering'],
            'motivation' => ['Why your campus?', 'Tell us why your campus should host a programme'],
        ],
    ];

    /** @return array<string, string> field name => coded default */
    public static function defaults(): array
    {
        return array_merge(...array_values(array_map(fn ($fields) => array_map(fn ($f) => $f[1], $fields), self::GROUPS)));
    }

    /** @return array<string, string> field name => placeholder shown on the site */
    public static function all(): array
    {
        $saved = array_filter((array) Settings::get('form_placeholders', []), fn ($v) => is_string($v) && trim($v) !== '');

        return array_replace(self::defaults(), array_intersect_key($saved, self::defaults()));
    }
}
