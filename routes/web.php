<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PlaceholderController;
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
        Route::get('/about', AboutController::class)->name('about');

        /*
        | Registered last, so it only ever sees what the routes above declined.
        | Every other public page still lives in the Next.js app; the navbar and
        | footer link to them, and a wall of 404s would make the shared layout
        | look broken. PlaceholderController says plainly that the page is not
        | ported yet. Drop this route as each page gets its own controller.
        */
        Route::get('/{path?}', PlaceholderController::class)
            ->where('path', '.*')
            ->name('placeholder');
    });

/*
| The CMS lives at /admin and is never locale-prefixed, same as before.
| Auth uses Laravel's session guard against the existing `user` table.
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
        Route::get('{resource}', ResourceController::class)
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

    $target = locale_path($request->path() === '/' ? '' : $request->path());
    $query = $request->getQueryString();

    return redirect($query ? $target.'?'.$query : $target);
});
