<?php

namespace App\Http\Controllers;

use App\Content\HomeContent;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;

/** Add-to-calendar for the summit and each agenda row: a standards-compliant .ics, works without JavaScript. */
class CalendarController extends Controller
{
    public function session(HomeContent $content, int $index): Response
    {
        $session = $content->agenda()['sessions'][$index] ?? abort(404);
        $summit = config('summit');

        [$from, $to] = explode('–', $session['time']);
        $day = CarbonImmutable::parse($summit['starts_at'])->setTimezone($summit['timezone'])->format('Y-m-d');

        return $this->ics("session-{$index}", $day, trim($from), trim($to), $session['title'], "{$session['type']} session (sample agenda).", $session['hall']);
    }

    /** The whole summit day, for the agenda page's header button. */
    public function summit(): Response
    {
        $summit = config('summit');
        $starts = CarbonImmutable::parse($summit['starts_at'])->setTimezone($summit['timezone']);

        return $this->ics('summit', $starts->format('Y-m-d'), $starts->format('H:i'), '18:30', null, 'Grand Summit day.', null);
    }

    private function ics(string $slug, string $day, string $from, string $to, ?string $title, string $description, ?string $hall): Response
    {
        $summit = config('summit');
        $at = fn (string $time) => CarbonImmutable::parse("{$day} {$time}", $summit['timezone'])->utc()->format('Ymd\THis\Z');
        $esc = fn (string $s) => addcslashes($s, ',;\\');
        $note = $summit['date_confirmed'] ? '' : ' Date and venue to be confirmed; this entry uses the placeholder date.';
        $name = "{$summit['name']} {$summit['edition']}";

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Freelancer Summit Bangladesh//Agenda//EN', 'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:fsb-'.$summit['edition'].'-'.$slug.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$at($from),
            'DTEND:'.$at($to),
            'SUMMARY:'.$esc($title ? "{$title} · {$name}" : $name),
            'DESCRIPTION:'.$esc($description.$note),
            'LOCATION:'.$esc(implode(', ', array_filter([$summit['venue'] ?: 'Venue to be confirmed', $hall, $summit['city']]))),
            'END:VEVENT', 'END:VCALENDAR', '',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="fsb-'.$slug.'.ics"',
        ]);
    }
}
