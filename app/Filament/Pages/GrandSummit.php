<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Support\Access;
use App\Support\Settings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use UnitEnum;

/**
 * The Grand Summit's edition details: date and venue (hero, countdown, agenda Day 1, calendar files, the programme
 * PDF), the tagline and the national journey counters. Saved to Settings › "summit" and laid over config/summit.php
 * on every request (Settings::applySummit), so a blank field keeps the .env / config value.
 */
class GrandSummit extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Grand Summit';

    protected static ?string $title = 'Grand Summit';

    protected static ?string $slug = 'grand-summit';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can(Access::permission('edit', 'programmes'));
    }

    public function mount(): void
    {
        $s = config('summit');
        $this->form->fill([
            'edition' => $s['edition'], 'starts_at' => $s['starts_at'], 'date_confirmed' => (bool) $s['date_confirmed'],
            'city' => $s['city'], 'venue' => $s['venue'], 'tagline' => $s['tagline'], 'tagline_final' => (bool) $s['tagline_final'],
            'counters' => $s['counters'],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Edition, date and venue')->columns(2)->columnSpanFull()->schema([
                TextInput::make('edition')->required()->maxLength(8)->helperText('The year in titles and the awards edition, e.g. 2026.'),
                DateTimePicker::make('starts_at')->label('Starts')->required()->seconds(false)->timezone(config('summit.timezone'))
                    ->helperText('Day 1 of the agenda, the hero countdown and the calendar files.'),
                Toggle::make('date_confirmed')->label('Date confirmed')->helperText('Off shows "date to be confirmed" and hides the countdown.'),
                TextInput::make('city')->required()->maxLength(80),
                TextInput::make('venue')->maxLength(160)->helperText('Blank shows "Venue to be confirmed".'),
            ]),
            Section::make('Tagline')->columns(2)->columnSpanFull()->schema([
                TextInput::make('tagline')->required()->maxLength(160),
                Toggle::make('tagline_final')->label('Tagline approved')->helperText('Off marks it as a working line.'),
            ]),
            Section::make('National journey counters')->description('The numbers on the landing and the national journey page.')->columns(4)->columnSpanFull()->schema([
                TextInput::make('counters.divisions_covered')->label('Divisions covered')->numeric()->minValue(0)->required(),
                TextInput::make('counters.districts_covered')->label('Districts covered')->numeric()->minValue(0)->required(),
                TextInput::make('counters.institutions_activated')->label('Institutions activated')->numeric()->minValue(0)->required(),
                TextInput::make('counters.participants_reached')->label('Participants reached')->numeric()->minValue(0)->required(),
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])->id('form')->livewireSubmitHandler('save')->footer([
                Actions::make([Action::make('save')->label('Save Grand Summit')->submit('save')->keyBindings(['mod+s'])]),
            ]),
        ]);
    }

    public function save(): void
    {
        $d = $this->form->getState();

        Settings::put('summit', [
            'edition' => (string) $d['edition'],
            'starts_at' => Carbon::parse($d['starts_at'])->timezone(config('summit.timezone'))->toIso8601String(),
            'date_confirmed' => (bool) $d['date_confirmed'],
            'city' => $d['city'],
            'venue' => filled($d['venue'] ?? null) ? $d['venue'] : null,
            'tagline' => $d['tagline'],
            'tagline_final' => (bool) $d['tagline_final'],
            'counters' => array_map('intval', $d['counters'] ?? []),
        ]);
        Settings::applySummit();

        AuditLog::record('Updated', 'Grand Summit', 'Saved the Grand Summit details');
        Notification::make()->success()->title('Grand Summit saved')->body('The website uses the new details from the next page load.')->send();
    }
}
