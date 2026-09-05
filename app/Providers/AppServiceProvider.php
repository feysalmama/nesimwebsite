<?php

namespace App\Providers;

use App\Models\GlobalSettings;
use App\Support\AdminNav;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * The navbar and footer are included by every public layout and both read
         * the globalsettings singleton. Sharing them here rather than passing them
         * from each controller means a page cannot render a broken header just
         * because someone forgot one argument — GlobalSettings::current() also
         * memoises, so this costs one query per request however many views ask.
         */
        View::composer(['partials.*', 'layouts.site'], function ($view) {
            $view->with('settings', GlobalSettings::current());
        });

        // The sidebar filters its groups by the signed-in user's role, exactly as
        // AdminSidebar did with the role from the next-auth session.
        View::composer('partials.admin.*', function ($view) {
            $view->with([
                'navGroups' => AdminNav::groupsForRole(Auth::user()?->role),
                'adminUser' => Auth::user(),
            ]);
        });
    }
}
