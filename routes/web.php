<?php

use App\Content\PageContent;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Dev\KitController;
use App\Http\Controllers\ExhibitEnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Every brief URL resolves; unbuilt pages share the "Coming next" page.
foreach (array_keys(PageContent::PAGES) as $path) {
    Route::get($path, PageController::class)->where('slug', '[a-z0-9-]+');
}

Route::post('/exhibition/enquiries', [ExhibitEnquiryController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('exhibition.enquiries.store');

Route::post('/locale', function (Request $request) {
    $request->validate(['locale' => ['required', 'in:'.implode(',', config('summit.locales'))]]);
    $request->session()->put('locale', $request->string('locale')->value());

    return back();
})->name('locale');

Route::get('/agenda/sessions/{index}.ics', [CalendarController::class, 'session'])->whereNumber('index')->name('agenda.ics');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/robots.txt', [SeoController::class, 'robots']);

// Inner-page kit preview, local only (docs/inner-design-kit.md).
if (app()->isLocal()) {
    Route::get('/_kit', [KitController::class, 'show']);
    Route::post('/_kit/form', [KitController::class, 'submit'])->middleware(['honeypot', 'throttle:forms']);
}
