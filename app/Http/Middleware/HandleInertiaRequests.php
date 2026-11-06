<?php

namespace App\Http\Middleware;

use App\Content\SiteContent;
use App\Support\FormPlaceholders;
use App\Support\Seo;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $site = app(SiteContent::class);
        $summit = config('summit');
        $identity = Settings::identity();

        return [
            ...parent::share($request),
            'locale' => app()->getLocale(),
            'summit' => [
                'name' => $identity['site_name'],
                'edition' => $summit['edition'],
                'startsAt' => $summit['starts_at'],
                'dateConfirmed' => $summit['date_confirmed'],
                'city' => $summit['city'],
                'venue' => $summit['venue'],
                'divisions' => $summit['divisions'],
                'districts' => $summit['districts'],
                'campuses' => $summit['campuses'],
                'tagline' => $identity['tagline'],
                'taglineFinal' => $summit['tagline_final'],
                'hashtag' => $summit['hashtag'],
                'social' => Settings::get('social', $summit['social']),
            ],
            'site' => [
                'navigation' => $site->navigation(),
                'navGroups' => $site->navGroups(),
                'headerLinks' => $site->headerLinks(),
                'ctas' => $site->ctas(),
                'organisers' => $site->organisers(),
                'contacts' => $site->contacts(),
                'legal' => $site->legal(),
                'importantLinks' => $site->importantLinks(),
                'url' => rtrim(config('app.url'), '/'),
            ],
            'placeholders' => FormPlaceholders::all(),
            'seo' => fn () => Seo::overrides($request->route()?->uri()),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'submitted' => fn () => $request->session()->get('submitted'),
            ],
        ];
    }
}
