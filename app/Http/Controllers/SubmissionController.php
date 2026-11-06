<?php

namespace App\Http\Controllers;

use App\Content\HomeContent;
use App\Enums\AttendeeType;
use App\Enums\Interest;
use App\Enums\ProgramType;
use App\Mail\SubmissionReceived;
use App\Models\AmbassadorLead;
use App\Models\AwardApplication;
use App\Models\AwardCategory;
use App\Models\ContactMessage;
use App\Models\EmailTemplate;
use App\Models\ExhibitionApplication;
use App\Models\ForumApplication;
use App\Models\JourneyProgram;
use App\Models\ProgramRegistration;
use App\Models\SponsorshipEnquiry;
use App\Models\SummitRegistration;
use App\Queries\ReferenceQuery;
use App\Rules\BdMobile;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Brief §8: separate journeys with their own fields, records, reference numbers and confirmation emails.
 * Each action validates, stores through IsSubmission::submit(), queues the branded confirmation and returns
 * to the page with `submitted` flashed, which the page swaps for its confirmation panel.
 */
class SubmissionController extends Controller
{
    /** Options shared by the forms and the validation (the page content reads the same lists). */
    public const EXPERIENCE = ['new' => 'Just starting', 'growing' => '1 to 3 years', 'established' => '3 years or more', 'agency' => 'I run a team or agency'];

    public const AVAILABILITY = ['full-time' => 'Full time', 'part-time' => 'Part time', 'project' => 'Project by project'];

    public const SECTORS = ['marketplace' => 'Marketplace or platform', 'bpo-ites' => 'BPO or ITES company', 'training' => 'Training institute or university', 'fintech' => 'Fintech or payments', 'software' => 'Software or AI company', 'government' => 'Government or development', 'other' => 'Other'];

    public const BOOTHS = ['standard' => 'Standard booth', 'large' => 'Large booth', 'custom' => 'Custom space', 'not-sure' => 'Not sure yet'];

    public const BUDGETS = ['under-5' => 'Under BDT 5 lakh', '5-15' => 'BDT 5 to 15 lakh', '15-30' => 'BDT 15 to 30 lakh', '30-plus' => 'Above BDT 30 lakh', 'discuss' => 'Prefer to discuss'];

    public const TOPICS = ['general' => 'General question', 'registration' => 'Summit registration', 'awards' => 'Awards and nominations', 'sponsorship' => 'Sponsorship', 'exhibition' => 'Exhibition', 'forum' => 'BACCO Freelancer Forum', 'media' => 'Media and press', 'programs' => 'District and campus programs'];

    public const PLATFORMS = ['upwork' => 'Upwork', 'fiverr' => 'Fiverr', 'freelancer' => 'Freelancer.com', 'linkedin' => 'LinkedIn', 'behance' => 'Behance', 'github' => 'GitHub', 'other' => 'Other'];

    public const AWARD_EXPERIENCE = ['under-1' => 'Less than 1 year', '1-2' => '1–2 years', '3-5' => '3–5 years', '6-10' => '6–10 years', '10-plus' => 'More than 10 years'];

    public const NATIONALITIES = ['bangladeshi' => 'Bangladeshi', 'other' => 'Other'];

    public const PROFILE_TYPES = ['freelancer' => 'Freelancer', 'student' => 'Student', 'professional' => 'Professional', 'other' => 'Other'];

    public const SKILLS = ['design' => 'Design', 'development' => 'Web and app development', 'marketing' => 'Digital marketing', 'writing' => 'Writing and translation', 'video' => 'Video and animation', 'data' => 'Data and AI', 'support' => 'Virtual assistance and support', 'other' => 'Other'];

    public function register(Request $request, ReferenceQuery $ref): RedirectResponse
    {
        $edition = (string) config('summit.edition');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('summit_registrations')->where('edition', $edition)->whereNull('deleted_at')],
            'mobile' => ['required', new BdMobile],
            'organisation' => ['required', 'string', 'max:160'],
            'designation' => ['required', 'string', 'max:120'],
            'district' => ['required', 'exists:districts,slug'],
            'attendee_type' => ['required', Rule::in(AttendeeType::values())],
            'interests' => ['required', 'array', 'min:1'],
            'interests.*' => [Rule::in(Interest::values())],
            'consent' => ['accepted'],
        ], ['email.unique' => 'This email is already registered for the summit. Check your inbox for the confirmation.'], $this->names());

        $row = SummitRegistration::submit([
            'edition' => $edition,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => BdMobile::normalize($data['mobile']),
            'organisation' => $data['organisation'] ?? null,
            'designation' => $data['designation'] ?? null,
            'district_id' => $ref->districtId($data['district']),
            'attendee_type' => $data['attendee_type'],
            'interests' => $data['interests'] ?? [],
        ], $request);

        return $this->done($data['email'], $row->reference, 'register', 'Summit registration received', receipt: ! self::confirmsRegistrations(), lines: [
            'Thank you for registering for Freelancer Summit Bangladesh. Keep this reference number for your records.',
            'We will email your e-ticket and venue details once the date and venue are confirmed.',
        ]);
    }

    public function nominate(Request $request, ReferenceQuery $ref): RedirectResponse
    {
        // The categories the form offers: open ones from Awards Management, else the coded list.
        $offered = app(\App\Content\Pages\AwardPages::class)->nominate()['categories'];
        $categories = array_column($offered, 'label');
        $slugs = array_column($offered, 'value');
        $self = $request->boolean('self_nominated', true);

        $data = $request->validate([
            'category' => ['required', Rule::in($slugs)],
            'self_nominated' => ['required', 'boolean'],
            'nominator_name' => [Rule::requiredIf(! $self), 'nullable', 'string', 'max:120'],
            'nominator_email' => [Rule::requiredIf(! $self), 'nullable', 'email', 'max:160'],
            'nominator_mobile' => [Rule::requiredIf(! $self), 'nullable', new BdMobile],
            'nominee_name' => ['required', 'string', 'max:120'],
            'nominee_email' => ['required', 'email', 'max:160'],
            'nominee_mobile' => ['required', new BdMobile],
            'district' => ['required', 'exists:districts,slug'],
            'headline' => ['required', 'string', 'max:160'],
            'experience' => ['required', Rule::in(array_keys(self::AWARD_EXPERIENCE))],
            'bio' => ['required', 'string', 'max:300'],
            'achievements' => ['required', 'string', 'max:2000'],
            'portfolio' => ['array', 'max:5'],
            'portfolio.*.url' => ['nullable', 'url:http,https', 'max:255'],
            'profiles' => ['array', 'max:5'],
            'profiles.*.platform' => ['nullable', Rule::in(array_keys(self::PLATFORMS))],
            'profiles.*.url' => ['nullable', 'url:http,https', 'max:255'],
            'files' => ['array', 'max:'.Uploads::MAX_FILES],
            'files.*' => Uploads::rules(),
            'declaration' => ['accepted'],
            'consent' => ['accepted'],
        ], [], $this->names());

        $row = DB::transaction(function () use ($data, $request, $ref, $categories, $slugs, $self) {
            $i = array_search($data['category'], $slugs, true);
            $category = AwardCategory::firstOrCreate(['slug' => $data['category']], ['name' => $categories[$i], 'sort' => $i]);

            $row = AwardApplication::submit([
                'award_category_id' => $category->id,
                'edition' => (string) config('summit.edition'),
                'self_nominated' => $self,
                'nominator_name' => $self ? null : $data['nominator_name'],
                'nominator_email' => $self ? null : $data['nominator_email'],
                'nominator_phone' => $self ? null : BdMobile::normalize($data['nominator_mobile']),
                'nominee_name' => $data['nominee_name'],
                'nominee_email' => $data['nominee_email'],
                'nominee_phone' => BdMobile::normalize($data['nominee_mobile']),
                'district_id' => $ref->districtId($data['district']),
                'headline' => $data['headline'],
                'experience' => $data['experience'],
                'bio' => $data['bio'],
                'achievements' => $data['achievements'],
                'portfolio_links' => $this->links($data['portfolio'] ?? []),
                'marketplace_profiles' => $this->links($data['profiles'] ?? [], true),
            ], $request);

            foreach ($request->file('files', []) as $file) {
                $row->files()->create(Uploads::store($file, "awards/{$row->reference}"));
            }

            return $row;
        });

        return $this->done($self ? $data['nominee_email'] : $data['nominator_email'], $row->reference, 'nominate', 'Award nomination received', [
            'Your Freelancer Awards application has been submitted. Status: Submitted.',
            'The organising team reviews every application. Shortlisted nominees are published on the awards page.',
        ]);
    }

    public function sponsor(Request $request): RedirectResponse
    {
        $tiers = array_map(fn ($t) => Str::slug($t), app(HomeContent::class)->sponsorship()['tiers']);
        $data = $request->validate([
            'company' => ['required', 'string', 'max:160'],
            'contact_person' => ['required', 'string', 'max:120'],
            'designation' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'mobile' => ['required', new BdMobile],
            'interest' => ['required', Rule::in([...$tiers, 'not-sure'])],
            'budget' => ['nullable', Rule::in(array_keys(self::BUDGETS))],
            'requirements' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:500'],
            'callback_requested' => ['boolean'],
            'consent' => ['accepted'],
        ], [], $this->names());

        $row = SponsorshipEnquiry::submit([...collect($data)->except(['mobile', 'consent'])->all(), 'phone' => BdMobile::normalize($data['mobile']), 'callback_requested' => (bool) ($data['callback_requested'] ?? false)], $request);

        return $this->done($data['email'], $row->reference, 'sponsor', 'Sponsorship enquiry received', [
            'Thank you for your interest in sponsoring Freelancer Summit Bangladesh.',
            'The partnerships team will contact you with the sponsorship prospectus and next steps.',
        ]);
    }

    public function exhibit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'organisation' => ['required', 'string', 'max:160'],
            'sector' => ['required', Rule::in(array_keys(self::SECTORS))],
            'booth_requirement' => ['nullable', Rule::in(array_keys(self::BOOTHS))],
            'contact_person' => ['required', 'string', 'max:120'],
            'designation' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'mobile' => ['required', new BdMobile],
            'products' => ['required', 'string', 'max:2000'],
            'branding_needs' => ['nullable', 'string', 'max:2000'],
            'requirements' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ], [], $this->names());

        $row = ExhibitionApplication::submit([...collect($data)->except(['mobile', 'consent'])->all(), 'phone' => BdMobile::normalize($data['mobile'])], $request);

        return $this->done($data['email'], $row->reference, 'exhibit', 'Exhibition application received', [
            'Thank you for applying to exhibit at Freelancer Summit Bangladesh.',
            'Booths are allocated once the floor plan is final. We will confirm your booth and send the next steps by email.',
        ]);
    }

    public function forum(Request $request, ReferenceQuery $ref): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('forum_applications')->whereNull('deleted_at')],
            'mobile' => ['required', new BdMobile],
            'district' => ['required', 'exists:districts,slug'],
            'nationality' => ['required', Rule::in(array_keys(self::NATIONALITIES))],
            'nid' => ['nullable', 'regex:/^(\d{10}|\d{13}|\d{17})$/'],
            'profile_type' => ['required', Rule::in(array_keys(self::PROFILE_TYPES))],
            'experience_level' => ['required', Rule::in(array_keys(self::EXPERIENCE))],
            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => [Rule::in(array_keys(self::SKILLS))],
            'services' => ['nullable', 'string', 'max:1000'],
            'availability' => ['nullable', Rule::in(array_keys(self::AVAILABILITY))],
            'profiles' => ['array', 'max:5'],
            'profiles.*.platform' => ['nullable', Rule::in(array_keys(self::PLATFORMS))],
            'profiles.*.url' => ['nullable', 'url:http,https', 'max:255'],
            'consent' => ['accepted'],
        ], ['email.unique' => 'An application with this email already exists. We will be in touch about its status.', 'nid.regex' => 'Enter a 10, 13 or 17 digit NID number.'], $this->names());

        $row = ForumApplication::submit([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => BdMobile::normalize($data['mobile']),
            'district_id' => $ref->districtId($data['district']),
            'nationality' => $data['nationality'],
            'nid' => $data['nid'] ?? null,
            'profile_type' => $data['profile_type'],
            'experience_level' => $data['experience_level'],
            'skills' => $data['skills'],
            'services' => $data['services'] ?? null,
            'availability' => $data['availability'] ?? null,
            'marketplace_profiles' => $this->links($data['profiles'] ?? [], true),
        ], $request);

        return $this->done($data['email'], $row->reference, 'forum', 'Forum application received', [
            'Thank you for applying to the BACCO Freelancer Forum.',
            'BACCO reviews applications once the membership rules are final, and will email you about your status.',
        ]);
    }

    public function program(Request $request, string $type, string $slug): RedirectResponse
    {
        $program = JourneyProgram::where('type', ProgramType::from($type))->where('slug', $slug)->firstOrFail();
        abort_unless($program->registration_open, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('program_registrations')->where('journey_program_id', $program->id)->whereNull('deleted_at')],
            'mobile' => ['required', new BdMobile],
            'institution' => ['nullable', 'string', 'max:160'],
            'consent' => ['accepted'],
        ], ['email.unique' => 'This email is already registered for this programme.'], $this->names());

        $row = ProgramRegistration::submit(['journey_program_id' => $program->id, 'name' => $data['name'], 'email' => $data['email'], 'phone' => BdMobile::normalize($data['mobile']), 'institution' => $data['institution'] ?? null], $request);

        return $this->done($data['email'], $row->reference, 'program', 'Programme registration received', receipt: ! self::confirmsRegistrations(), lines: [
            "You are registered for {$program->title}.",
            'We will email the final time and venue details before the programme.',
        ]);
    }

    public function ambassador(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'institution' => ['required', 'string', 'max:160'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'mobile' => ['required', new BdMobile],
            'department' => ['nullable', 'string', 'max:120'],
            'motivation' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'],
        ], [], $this->names());

        $row = AmbassadorLead::submit([...collect($data)->except(['mobile', 'consent'])->all(), 'phone' => BdMobile::normalize($data['mobile'])], $request);

        return $this->done($data['email'], $row->reference, 'ambassador', 'Campus ambassador interest received', [
            'Thank you for offering to bring the summit to your campus.',
            'The campus team will contact you when programmes are scheduled for your institution.',
        ]);
    }

    public function contact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'topic' => ['required', Rule::in(array_keys(self::TOPICS))],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'mobile' => ['nullable', new BdMobile],
            'message' => ['required', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ], [], $this->names());

        $row = ContactMessage::submit([...collect($data)->except(['mobile', 'consent'])->all(), 'phone' => filled($data['mobile'] ?? null) ? BdMobile::normalize($data['mobile']) : null], $request);
        Mail::to(config('consent.organiser_email'))->queue(new SubmissionReceived('New contact message', $row->reference, [self::TOPICS[$data['topic']].' from '.$data['name'].' ('.$data['email'].')', $data['message']]));

        return $this->done($data['email'], $row->reference, 'contact', 'Message received', [
            'Thank you for contacting Freelancer Summit Bangladesh.',
            'The right person in the team will reply by email.',
        ]);
    }

    /** Keep only filled links, as [{platform?, url}] rows. */
    private function links(array $rows, bool $platform = false): array
    {
        return array_values(array_map(
            fn ($r) => $platform ? ['platform' => $r['platform'] ?? null, 'url' => $r['url']] : ['url' => $r['url']],
            array_filter($rows, fn ($r) => filled($r['url'] ?? null)),
        ));
    }

    /** `receipt: false` when the admin "Registration confirmed" email already goes to this person (no duplicate). */
    private function done(string $email, string $reference, string $form, string $heading, array $lines, bool $receipt = true): RedirectResponse
    {
        if ($receipt) {
            Mail::to($email)->queue(new SubmissionReceived($heading, $reference, $lines));
        }

        return back()->with('submitted', ['form' => $form, 'reference' => $reference]);
    }

    /** The "Registration confirmed" template (Messages › Email templates) is switched on. */
    private static function confirmsRegistrations(): bool
    {
        return EmailTemplate::query()->where('key', 'registration_confirmed')->where('enabled', true)->exists();
    }

    /** Human field names for the error summary. */
    private function names(): array
    {
        return [
            'name' => 'full name', 'email' => 'email', 'mobile' => 'mobile number', 'district' => 'district', 'attendee_type' => 'attendee type',
            'nominee_name' => 'nominee name', 'nominee_email' => 'nominee email', 'nominee_mobile' => 'nominee mobile number',
            'nominator_name' => 'your name', 'nominator_email' => 'your email', 'nominator_mobile' => 'your mobile number',
            'nationality' => 'nationality', 'nid' => 'NID number', 'profile_type' => 'profile type',
            'headline' => 'professional title', 'experience' => 'years of freelancing experience', 'bio' => 'short bio',
            'achievements' => 'achievements', 'category' => 'award category', 'declaration' => 'declaration', 'consent' => 'consent',
            'company' => 'company', 'contact_person' => 'contact person', 'interest' => 'sponsorship interest', 'organisation' => 'organisation',
            'sector' => 'sector', 'products' => 'products or services', 'experience_level' => 'experience', 'skills' => 'skills',
            'institution' => 'institution', 'topic' => 'topic', 'message' => 'message',
            'portfolio.*.url' => 'portfolio link', 'profiles.*.url' => 'profile link', 'files.*' => 'document',
        ];
    }
}
