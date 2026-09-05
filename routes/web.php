<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\DonateController;
use App\Http\Controllers\Site\FaqController;
use App\Http\Controllers\Site\GalleryController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ImpactController;
use App\Http\Controllers\Site\InsightController;
use App\Http\Controllers\Site\LeadershipController;
use App\Http\Controllers\Site\MembershipController;
use App\Http\Controllers\Site\NewsController;
use App\Http\Controllers\Site\ProgramController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\RegisterController;
use App\Http\Controllers\Site\ResourceController;
use App\Http\Controllers\Site\ServiceController;
use App\Http\Controllers\Site\TestimonialController;
use App\Http\Controllers\Site\VolunteerController;
use App\Support\AdminNav;
use App\Support\LocaleText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
| The public site is always locale-prefixed, mirroring next-intl's
| localePrefix: "always" in the Next.js app. /en, /am and /om each serve the
| full site, so every URL that is already indexed keeps working unchanged.
*/

Route::get('/', fn () => redirect(locale_path()))->name('root');

Route::prefix('{locale}')
    ->whereIn('locale', LocaleText::LOCALES)
    ->middleware('locale')
    ->name('site.')
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        /*
        | The twenty-two pages under app/[locale]/ in the Next.js app, at the
        | paths they were served from. Nothing here is new: every URL that was
        | already indexed resolves to the same URL, and the ones that took a
        | submission take it on the same path the form is rendered on, which is
        | what the React forms posted to as well.
        */
        Route::get('/about', AboutController::class)->name('about');
        Route::get('/leadership', LeadershipController::class)->name('leadership');
        Route::get('/programs', ProgramController::class)->name('programs');
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects');
        Route::get('/services', [ServiceController::class, 'index'])->name('services');
        Route::get('/news', [NewsController::class, 'index'])->name('news');
        Route::get('/blog', [BlogController::class, 'index'])->name('blog');
        Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
        Route::get('/resources', ResourceController::class)->name('resources');
        Route::get('/testimonials', TestimonialController::class)->name('testimonials');
        Route::get('/insights', InsightController::class)->name('insights');
        Route::get('/faq', FaqController::class)->name('faq');

        /*
        | /impact had no page in the Next.js app - the header linked to it and it
        | 404'd - so this is built rather than ported. See ImpactController.
        */
        Route::get('/impact', ImpactController::class)->name('impact');

        /*
        | Detail pages. {slug} is a slug column and {id} a cuid, but neither is
        | constrained here: an unmatched row already 404s in the controller's
        | firstOrFail(), and a pattern would only turn "not in the database" into
        | "not a valid shape", which is a worse message for a stale link.
        */
        Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('project');
        Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('service');
        Route::get('/news/{id}', [NewsController::class, 'show'])->name('newsPost');
        Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blogPost');
        Route::get('/gallery/{id}', [GalleryController::class, 'show'])->name('galleryItem');

        /*
        | The five submission forms. GET renders the page, POST to the same path
        | stores the row - the pair replacing one /api/{name} route each. They sit
        | last only so the reading order is pages, then detail, then forms.
        */
        Route::get('/contact', ContactController::class)->name('contact');
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

        Route::get('/donate', DonateController::class)->name('donate');
        Route::post('/donate', [DonateController::class, 'store'])->name('donate.store');

        Route::get('/membership', MembershipController::class)->name('membership');
        Route::post('/membership', [MembershipController::class, 'store'])->name('membership.store');

        Route::get('/volunteer', VolunteerController::class)->name('volunteer');
        Route::post('/volunteer', [VolunteerController::class, 'store'])->name('volunteer.store');

        Route::get('/register', RegisterController::class)->name('register');
        Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

        /*
        | No catch-all. Every page the site links to is above, so an unknown
        | /{locale}/... path falls through to the global fallback, which 404s it
        | because the first segment is already a locale. PlaceholderController
        | used to answer these; it is gone with the last unported page.
        */
    });

/*
| The CMS lives at /admin and is never locale-prefixed, same as before.
| Auth uses Laravel's session guard against the `users` table.
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        // Single upload endpoint, same contract as the old POST /api/upload:
        // accepts a "file" field and returns {"url": "/uploads/..."}.
        Route::post('upload', UploadController::class)->name('upload');

        /*
        | One route for every ported module, constrained to the slugs that have
        | a spec. This is the last route in the group so `login`, `/`, `logout`
        | and `upload` are matched first, and the whereIn is what stops an
        | unported slug from reaching the controller — it falls through to the
        | fallback below and 404s, same as any other unknown /admin path.
        |
        | Adding a module means adding a spec class and one entry in
        | AdminNav::SPECS. This file does not change.
        */
        Route::get('{resource}', AdminResourceController::class)
            ->whereIn('resource', array_keys(AdminNav::specs()))
            ->name('resource');
    });
});

/*
| An unprefixed public path (/about, /programs, ...) is redirected into the
| default locale, which is what next-intl did.
|
| Reserved prefixes and any path containing a dot are deliberately left to 404.
| Redirecting a static asset to /en/logo.png is exactly the bug that broke
| next/image in the Next.js build, so the same guard is applied here from day
| one rather than rediscovered later.
*/
Route::fallback(function (Request $request) {
    $reserved = ['admin', 'uploads', 'storage', 'livewire', '_ignition', 'up', 'vendor', 'build'];

    if (in_array($request->segment(1), $reserved, true) || str_contains($request->path(), '.')) {
        abort(404);
    }

    if (! $request->isMethod('GET')) {
        abort(404);
    }

    /*
     * Already locale-prefixed, so there is nothing left to redirect to: the
     * locale group above matched every page it serves and declined this one.
     * Prefixing again would send /en/nothing to /en/en/nothing, which declines
     * it again and loops until the browser gives up. This guard is what stands
     * between an unknown /{locale}/... path and that loop now that the
     * PlaceholderController catch-all is gone.
     */
    if (in_array($request->segment(1), LocaleText::LOCALES, true)) {
        abort(404);
    }

    $target = locale_path($request->path() === '/' ? '' : $request->path());
    $query = $request->getQueryString();

    return redirect($query ? $target.'?'.$query : $target);
});
