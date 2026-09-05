<?php

namespace App\Http\Middleware;

use App\Support\LocaleText;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mirrors next-intl's `localePrefix: "always"` from the old i18n.ts: every
 * public URL carries its locale as the first segment, and that segment selects
 * both the translation file (lang/{locale}.json) and the language used to read
 * the locale-JSON columns stored in the database.
 *
 * It is registered on the site route group only, never globally. /admin,
 * /uploads and /livewire must not be locale-prefixed — prefixing static assets
 * is exactly the bug that broke next/image in the Next.js version.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        // The route constraint already limits this to en|am|om; the check is
        // here so the middleware cannot silently render an unknown language.
        if (! in_array($locale, LocaleText::LOCALES, true)) {
            abort(404);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
