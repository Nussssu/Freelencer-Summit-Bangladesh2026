<?php

use App\Content\InnerPageContent;
use App\Content\PageContent;
use App\Http\Controllers\AgendaDownloadController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\Dev\KitController;
use App\Http\Controllers\ExhibitEnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InnerPageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Every brief URL resolves; built pages get their own controller, unbuilt ones share the "Coming next" page.
foreach (array_keys(InnerPageContent::PAGES) as $path) {
    Route::get($path, InnerPageController::class);
}
foreach (array_diff(array_keys(PageContent::PAGES), array_keys(InnerPageContent::PAGES)) as $path) {
    Route::get($path, PageController::class)->where('slug', '[a-z0-9-]+');
}

// The award categories page was folded into /awards; old links land on its categories section.
Route::permanentRedirect('/awards/categories', '/awards#categories');

// Brief §8: one journey per form, each with its own record, reference number and confirmation.
Route::middleware(['honeypot', 'throttle:forms'])->controller(SubmissionController::class)->group(function () {
    Route::post('/register', 'register')->name('submit.register');
    Route::post('/awards/nominate', 'nominate')->name('submit.nominate');
    Route::post('/sponsorship-opportunity', 'sponsor')->name('submit.sponsor');
    Route::post('/exhibition/apply', 'exhibit')->name('submit.exhibit');
    Route::post('/forum/register', 'forum')->name('submit.forum');
    Route::post('/contact', 'contact')->name('submit.contact');
    Route::post('/campus-programs/ambassadors', 'ambassador')->name('submit.ambassador');
    Route::post('/programs/{type}/{slug}/register', 'program')->whereIn('type', ['divisional-summit', 'district-roadshow', 'campus-program'])->name('submit.program');
});

Route::post('/exhibition/enquiries', [ExhibitEnquiryController::class, 'store'])
    ->middleware(['throttle:5,1', 'recaptcha'])
    ->name('exhibition.enquiries.store');

Route::post('/locale', function (Request $request) {
    $request->validate(['locale' => ['required', 'in:'.implode(',', config('summit.locales'))]]);
    $request->session()->put('locale', $request->string('locale')->value());

    return back();
})->name('locale');

Route::get('/agenda/summit-programme.pdf', AgendaDownloadController::class)->name('agenda.pdf');
Route::get('/agenda/summit.ics', [CalendarController::class, 'summit'])->name('agenda.summit.ics');
Route::get('/agenda/sessions/{index}.ics', [CalendarController::class, 'session'])->whereNumber('index')->name('agenda.ics');

// Award applicants' private documents, for signed-in admins with the nominations view permission.
Route::get('/admin/award-files/{file}', \App\Http\Controllers\Admin\AwardFileController::class)->name('admin.award-files.show');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/robots.txt', [SeoController::class, 'robots']);

// Pages copied in the CMS live at their own URLs; anything else unknown is a 404.
Route::fallback([InnerPageController::class, 'copy']);

// Inner-page kit preview, local only (docs/inner-design-kit.md).
if (app()->isLocal()) {
    Route::get('/_kit', [KitController::class, 'show']);
    Route::post('/_kit/form', [KitController::class, 'submit'])->middleware(['honeypot', 'throttle:forms', 'recaptcha']);
}
