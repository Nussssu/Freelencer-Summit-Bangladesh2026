<?php

namespace App\Http\Controllers;

use App\Content\HomeContent;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;

/** Add-to-calendar for agenda rows: a standards-compliant .ics, works without JavaScript. */
class CalendarController extends Controller
{
    public function session(HomeContent $content, int $index): Response
    {
        $session = $content->agenda()['sessions'][$index] ?? abort(404);
        $summit = config('summit');

        [$from, $to] = explode('–', $session['time']);
        $day = CarbonImmutable::parse($summit['starts_at'])->setTimezone($summit['timezone'])->format('Y-m-d');
        $at = fn (string $time) => CarbonImmutable::parse("{$day} {$time}", $summit['timezone'])->utc()->format('Ymd\THis\Z');
        $esc = fn (string $s) => addcslashes($s, ',;\\');
        $note = $summit['date_confirmed'] ? '' : ' Date and venue to be confirmed; this entry uses the placeholder date.';

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Freelancer Summit Bangladesh//Agenda//EN', 'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:fsb-'.$summit['edition'].'-session-'.$index.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$at(trim($from)),
            'DTEND:'.$at(trim($to)),
            'SUMMARY:'.$esc("{$session['title']} · {$summit['name']} {$summit['edition']}"),
            'DESCRIPTION:'.$esc("{$session['type']} session (sample agenda).{$note}"),
            'LOCATION:'.$esc(($summit['venue'] ?? 'Venue to be confirmed').", {$session['hall']}, {$summit['city']}"),
            'END:VEVENT', 'END:VCALENDAR', '',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="fsb-session-'.$index.'.ics"',
        ]);
    }
}
