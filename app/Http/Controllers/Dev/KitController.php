<?php

namespace App\Http\Controllers\Dev;

use App\Enums\AttendeeType;
use App\Enums\Interest;
use App\Enums\ProgramType;
use App\Http\Controllers\Controller;
use App\Queries\ContentQuery;
use App\Queries\ExhibitorQuery;
use App\Queries\NewsQuery;
use App\Queries\ProgramQuery;
use App\Queries\ReferenceQuery;
use App\Queries\SpeakerQuery;
use App\Rules\BdMobile;
use App\Support\Seo;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Local-only preview of the inner-page kit (/_kit) with live directory + a validating demo form. */
class KitController extends Controller
{
    public function show(Request $request, SpeakerQuery $speakers, ReferenceQuery $ref, ContentQuery $content): Response
    {
        $filters = ['q' => (string) $request->query('q', ''), 'country' => (string) $request->query('country', '')];

        return Inertia::render('dev/Kit', [
            'filters' => $filters,
            'speakers' => fn () => $speakers->directory($filters, 3),
            'programs' => app(ProgramQuery::class)->list(ProgramType::DistrictRoadshow, [], 3)['data'],
            'news' => app(NewsQuery::class)->list(null, 3)['data'],
            'exhibitors' => app(ExhibitorQuery::class)->directory([], 2)['data'],
            'downloads' => $content->downloads(),
            'districts' => $ref->districts(),
            'attendeeTypes' => AttendeeType::options(),
            'interests' => Interest::options(),
            'consent' => ['version' => config('consent.version'), 'text' => config('consent.texts.'.config('consent.version'))],
        ])->withViewData(['jsonLd' => Seo::graph(Seo::breadcrumbs(['Home' => '/', 'Kit' => '/_kit']))]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'mobile' => ['required', new BdMobile],
            'district' => ['required', 'exists:districts,slug'],
            'attendee_type' => ['required', 'in:'.implode(',', AttendeeType::values())],
            'interests' => ['array'],
            'interests.*' => ['in:'.implode(',', Interest::values())],
            'about' => ['nullable', 'string', 'max:500'],
            'links' => ['array', 'max:5'],
            'links.*.url' => ['nullable', 'url:http,https', 'max:255'],
            'files' => ['array', 'max:'.Uploads::MAX_FILES],
            'files.*' => Uploads::rules(),
            'consent' => ['accepted'],
        ]);

        return back()->with('success', 'Kit form valid. Mobile normalised to '.BdMobile::normalize($data['mobile']).'. Nothing was saved.');
    }
}
