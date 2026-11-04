<?php

namespace App\Http\Controllers;

use App\Content\InnerPageContent;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Response;

/** The full summit programme as a PDF, built from the same sessions as the agenda page, so it never goes stale. */
class AgendaDownloadController extends Controller
{
    public function __invoke(InnerPageContent $content): Response
    {
        $summit = config('summit');
        $starts = CarbonImmutable::parse($summit['starts_at'])->setTimezone($summit['timezone']);
        $sessions = collect($content->agendaSessions())->groupBy('day');

        // Day N is starts_at + N-1, printed only once the date is confirmed.
        $days = array_map(fn ($day, $i) => [
            'label' => $day,
            'date' => $summit['date_confirmed'] ? $starts->addDays($i)->format('l, j F Y') : 'Date to be confirmed',
            'sessions' => $sessions->get($day, collect())->all(),
        ], InnerPageContent::AGENDA_DAYS, array_keys(InnerPageContent::AGENDA_DAYS));

        return Pdf::loadView('pdf.agenda', [
            'name' => "{$summit['name']} {$summit['edition']}",
            'place' => implode(', ', array_filter([$summit['venue'] ?: 'Venue to be confirmed', $summit['city']])),
            'days' => $days,
        ])->setPaper('a4')->download("fsb-{$summit['edition']}-summit-programme.pdf");
    }
}
