<?php

namespace App\Filament\Pages;

use App\Content\SiteContent;
use App\Filament\Fields\MediaPicker;
use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Models\AuditLog;
use App\Support\Access;
use App\Support\Settings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Throwable;
use UnitEnum;

/**
 * Identity, global header and footer content, social links, SEO defaults, reCAPTCHA and email delivery. Each list saves under the SiteContent method
 * of the same name with the same shape, so the Vue layout reads it unchanged.
 */
class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Global settings';

    /** Settings keys that mirror SiteContent methods one to one. */
    private const LISTS = ['headerLinks', 'navigation', 'ctas', 'organisers', 'contacts', 'importantLinks', 'legal'];

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can(Access::permission('edit', 'settings'));
    }

    public function mount(): void
    {
        $site = app(SiteContent::class);
        $mail = Settings::get('mail', []);
        unset($mail['smtp']['password']); // never sent back to the browser; blank keeps the saved one
        $recaptcha = Settings::get('recaptcha', []);
        unset($recaptcha['secret_key']); // same rule as the SMTP password

        $this->form->fill([
            ...collect(self::LISTS)->mapWithKeys(fn ($k) => [$k => $site->{$k}()])->all(),
            'identity' => Settings::identity(),
            'social' => Settings::get('social', config('summit.social')),
            'seo' => Settings::get('seo', []),
            'recaptcha' => $recaptcha + ['enabled' => false, 'version' => 'v3', 'threshold' => 0.5],
            'mail' => $mail + ['admin_emails' => [], 'from_address' => config('mail.from.address'), 'from_name' => config('mail.from.name')],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $link = fn (string $name, string $label) => Repeater::make($name)->label($label)->schema([
            TextInput::make('label')->required()->maxLength(60),
            TextInput::make('href')->label('Link')->required()->maxLength(255)->placeholder('/agenda or https://…'),
        ])->columns(2)->reorderable()->collapsible()->itemLabel(fn (array $state) => $state['label'] ?? null)->defaultItems(0);

        $tones = ['gold' => 'Gold (primary)', 'outline' => 'Outline', 'red' => 'Red', 'green' => 'Teal', 'blue' => 'BACCO blue', 'ghost' => 'Ghost'];
        $cta = fn (string $name, string $label) => Repeater::make($name)->label($label)->schema([
            TextInput::make('id')->label('Key')->required()->alphaDash()->maxLength(30)->helperText('Used by the code to find this button, e.g. register.'),
            TextInput::make('label')->required()->maxLength(60),
            TextInput::make('href')->label('Link')->required()->maxLength(255),
            Select::make('tone')->options($tones)->required()->native(false),
        ])->columns(4)->reorderable()->collapsible()->itemLabel(fn (array $state) => $state['label'] ?? null);

        return $schema->statePath('data')->components([
            Tabs::make()->persistTabInQueryString()->columnSpanFull()->tabs([
                Tab::make('General & identity')->icon(Heroicon::OutlinedIdentification)->schema([
                    Grid::make(2)->schema([
                        TextInput::make('identity.site_name')->label('Site name')->required()->maxLength(80)
                            ->helperText('Used in page titles (" · Site name"), emails and the browser tab.'),
                        TextInput::make('identity.tagline')->label('Tagline')->maxLength(160),
                        MediaPicker::make('identity.logo')->label('Logo (for light backgrounds)')->helperText('Used where the CMS renders the brand, e.g. admin pages. The site header keeps its designed wordmark.'),
                        MediaPicker::make('identity.logo_dark')->label('Logo (for dark backgrounds)')->helperText('Shown at the top of every email. Blank shows the site name as text.'),
                        MediaPicker::make('identity.favicon')->label('Favicon')->helperText('Square SVG or PNG. Blank keeps /favicon.svg.'),
                    ]),
                    Section::make('Contact')->description('Shown in the footer of every email.')->columns(2)->schema([
                        TextInput::make('identity.phone')->label('Contact phone')->tel()->maxLength(40),
                        TextInput::make('identity.email')->label('Contact email')->email()->maxLength(160),
                        Textarea::make('identity.address')->label('Office address')->rows(2)->maxLength(300)->columnSpanFull(),
                    ]),
                ]),
                Tab::make('Header')->icon(Heroicon::OutlinedBars3)->schema([
                    $link('headerLinks', 'Header links (the row under the logo)')->maxItems(7),
                    Repeater::make('navigation')->label('Full menu (opens from "Menu")')->schema([
                        TextInput::make('label')->required()->maxLength(60),
                        TextInput::make('href')->label('Link')->required()->maxLength(255),
                        Select::make('group')->options(collect(app(SiteContent::class)->navGroups())->pluck('label', 'id'))->required()->native(false),
                    ])->columns(3)->reorderable()->collapsible()->collapsed()->itemLabel(fn (array $state) => $state['label'] ?? null),
                    Section::make('Call-to-action buttons')->description('Primary buttons appear in the header "Get involved" menu and across pages.')->schema([
                        $cta('ctas.primary', 'Primary'),
                        $cta('ctas.secondary', 'Secondary'),
                    ]),
                ]),
                Tab::make('Footer')->icon(Heroicon::OutlinedQueueList)->schema([
                    Repeater::make('contacts')->label('Contact lines')->schema([
                        TextInput::make('label')->required()->maxLength(60),
                        TextInput::make('value')->label('Email or phone')->maxLength(120)->helperText('Blank shows "TBA".'),
                    ])->columns(2)->reorderable()->itemLabel(fn (array $state) => $state['label'] ?? null),
                    $link('importantLinks', 'Important links'),
                    $link('legal', 'Legal links'),
                    Repeater::make('organisers')->label('Organisers (header and footer)')->schema([
                        TextInput::make('short')->label('Short name')->required()->maxLength(20),
                        TextInput::make('name')->label('Full name')->required()->maxLength(160),
                        TextInput::make('role')->required()->maxLength(60),
                        MediaPicker::make('logo')->label('Logo'),
                    ])->columns(2)->reorderable()->minItems(2)->itemLabel(fn (array $state) => $state['short'] ?? null),
                ]),
                Tab::make('Social')->icon(Heroicon::OutlinedShare)->schema([
                    Grid::make(2)->schema([
                        TextInput::make('social.facebook')->label('Facebook')->url()->maxLength(255),
                        TextInput::make('social.linkedin')->label('LinkedIn')->url()->maxLength(255),
                        TextInput::make('social.youtube')->label('YouTube')->url()->maxLength(255),
                        TextInput::make('social.x')->label('X (Twitter)')->url()->maxLength(255),
                        TextInput::make('social.instagram')->label('Instagram')->url()->maxLength(255),
                        TextInput::make('social.tiktok')->label('TikTok')->url()->maxLength(255),
                    ]),
                    Text::make('The site footer currently shows Facebook, LinkedIn, YouTube and X icons. Instagram and TikTok are saved and shared with the site, ready for when their icons are added to the design.')->color('gray'),
                ]),
                Tab::make('SEO & meta')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                    TextInput::make('seo.title')->label('Default meta title')->maxLength(70)
                        ->helperText('Home page title when Pages › Home has no meta title of its own. Inner pages keep their own titles.'),
                    Textarea::make('seo.description')->label('Default meta description')->rows(3)->maxLength(320)
                        ->helperText('Home page description, same rule as the title.'),
                    MediaPicker::make('seo.og_image')->label('Default share image (Open Graph)')
                        ->helperText('Every page without its own share image. Best at 1200×630. Blank keeps /og-image.png.'),
                ]),
                Tab::make('Security')->icon(Heroicon::OutlinedShieldCheck)->schema([
                    Section::make('Google reCAPTCHA')->description('Protects the public forms. Keys come from google.com/recaptcha/admin; the key type must match the version.')->columns(2)->schema([
                        Toggle::make('recaptcha.enabled')->label('Protect forms with reCAPTCHA')->columnSpanFull(),
                        Select::make('recaptcha.version')->label('Version')->native(false)->required()
                            ->options(['v3' => 'v3 (score, invisible)', 'v2' => 'v2 Invisible']),
                        TextInput::make('recaptcha.threshold')->label('v3 score threshold')->numeric()->minValue(0)->maxValue(1)->step(0.1)
                            ->helperText('0 = allow all, 1 = strictest. Google suggests 0.5.'),
                        TextInput::make('recaptcha.site_key')->label('Site key')->requiredIf('recaptcha.enabled', true)->maxLength(100),
                        TextInput::make('recaptcha.secret_key')->label('Secret key')->password()->revealable()->autocomplete('new-password')->maxLength(100)
                            ->helperText(fn () => isset(Settings::get('recaptcha', [])['secret_key']) ? 'Saved. Leave blank to keep it.' : null),
                    ]),
                ]),
                Tab::make('Email')->icon(Heroicon::OutlinedEnvelope)->schema([
                    Section::make('Email templates')->schema([
                        Text::make('Subjects and bodies of the automatic emails (New lead alert, Enquiry received, Registration confirmed) are edited in Settings › Email templates.'),
                        Actions::make([Action::make('templates')->label('Edit email templates')->color('gray')->url(fn () => EmailTemplateResource::getUrl())]),
                    ]),
                    Section::make('Lead notifications')->schema([
                        TagsInput::make('mail.admin_emails')->label('Admin inboxes')->placeholder('Add an email and press Enter')
                            ->nestedRecursiveRules(['email'])->helperText('Every form submission is summarised to these addresses.'),
                        Grid::make(2)->schema([
                            TextInput::make('mail.from_address')->label('Sender address')->email(),
                            TextInput::make('mail.from_name')->label('Sender name')->maxLength(80),
                        ]),
                    ]),
                    Section::make('SMTP server')->description('Leave the host blank to use the server settings in .env.')->columns(2)->schema([
                        TextInput::make('mail.smtp.host')->label('Host')->placeholder('smtp.example.com'),
                        TextInput::make('mail.smtp.port')->label('Port')->numeric()->placeholder('587'),
                        TextInput::make('mail.smtp.username')->label('Username')->autocomplete('off'),
                        TextInput::make('mail.smtp.password')->label('Password')->password()->revealable()->autocomplete('new-password')
                            ->helperText(fn () => isset(Settings::get('mail', [])['smtp']['password']) ? 'Saved. Leave blank to keep it.' : null),
                        Select::make('mail.smtp.scheme')->label('Encryption')->options(['smtp' => 'STARTTLS (587)', 'smtps' => 'SSL/TLS (465)'])->placeholder('Default'),
                    ]),
                ]),
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])->id('form')->livewireSubmitHandler('save')->footer([
                Actions::make([
                    Action::make('save')->label('Save settings')->submit('save')->keyBindings(['mod+s']),
                    Action::make('test')->label('Send test email to me')->color('gray')->action('sendTest'),
                ]),
            ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (array_diff(self::LISTS, ['ctas']) as $key) {
            Settings::put($key, array_values($data[$key] ?? []));
        }
        Settings::put('ctas', ['primary' => array_values($data['ctas']['primary'] ?? []), 'secondary' => array_values($data['ctas']['secondary'] ?? [])]);
        Settings::put('social', array_map(fn ($v) => $v ?: null, $data['social'] ?? []));
        Settings::put('identity', $data['identity'] ?? []);
        Settings::put('seo', array_filter($data['seo'] ?? []));

        $recaptcha = $data['recaptcha'] ?? [];
        $savedSecret = Settings::get('recaptcha', [])['secret_key'] ?? null;
        $recaptcha['secret_key'] = filled($recaptcha['secret_key'] ?? null) ? encrypt($recaptcha['secret_key']) : $savedSecret;
        Settings::put('recaptcha', $recaptcha);

        $mail = $data['mail'] ?? [];
        $saved = Settings::get('mail', [])['smtp']['password'] ?? null;
        $mail['smtp']['password'] = filled($mail['smtp']['password'] ?? null) ? encrypt($mail['smtp']['password']) : $saved;
        Settings::put('mail', $mail);

        AuditLog::record('Updated', 'Settings', 'Saved site settings');
        Notification::make()->success()->title('Settings saved')->body('The website uses them from the next page load.')->send();
    }

    public function sendTest(): void
    {
        $to = auth()->user()->email;
        try {
            Settings::applyMail();
            Mail::purge();
            Mail::raw('This is a test from the Freelancer Summit CMS. Email delivery works.', fn ($m) => $m->to($to)->subject('CMS test email'));
            Notification::make()->success()->title("Test sent to {$to}")->send();
        } catch (Throwable $e) {
            Notification::make()->danger()->title('Sending failed')->body($e->getMessage())->persistent()->send();
        }
    }
}
