<?php

namespace App\Filament\Pages;

use App\Content\SiteContent;
use App\Filament\Fields\MediaPicker;
use App\Models\AuditLog;
use App\Support\Access;
use App\Support\Settings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Global header and footer content. Each list saves under the SiteContent method of the same name with the same shape,
 * so the Vue layout reads it unchanged.
 */
class HeaderFooter extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 1; // directly above Settings

    protected static ?string $navigationLabel = 'Header & Footer';

    protected static ?string $title = 'Header & Footer';

    protected static ?string $slug = 'header-footer';

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
        $this->form->fill(collect(self::LISTS)->mapWithKeys(fn ($k) => [$k => $site->{$k}()])->all());
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
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])->id('form')->livewireSubmitHandler('save')->footer([
                Actions::make([Action::make('save')->label('Save header & footer')->submit('save')->keyBindings(['mod+s'])]),
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

        AuditLog::record('Updated', 'Settings', 'Saved header & footer');
        Notification::make()->success()->title('Header & footer saved')->body('The website uses them from the next page load.')->send();
    }
}
