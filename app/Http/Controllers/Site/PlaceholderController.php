<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\LocaleText;
use App\Support\SiteNav;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Stands in for the public pages that have not been ported yet.
 *
 * The navbar and footer link to roughly twenty routes that still only exist in
 * the Next.js app. Letting them 404 would make the vertical slice look broken
 * and would hide the fact that the shared layout works; rendering the real page
 * for all of them is the remaining work, not this slice. So they land here, with
 * the ported label from SiteNav where one exists and a link back to a page that
 * does render.
 */
class PlaceholderController extends Controller
{
    public function __invoke(Request $request): View
    {
        $path = trim($request->path(), '/');

        /*
         * The catch-all that routes here matches ".*", so it would also claim
         * /en/logo.png and answer it with an HTML page. Anything with a dot in
         * the last segment is a file request and must 404 like a file.
         */
        if (str_contains(basename($path), '.')) {
            abort(404);
        }

        // Strip the locale prefix; the middleware has already validated it.
        $segments = explode('/', $path);

        if (in_array($segments[0], LocaleText::LOCALES, true)) {
            array_shift($segments);
        }

        $slug = $segments[0] ?? '';

        return view('site.placeholder', [
            'slug' => $slug,
            'label' => $this->label($slug),
            'remainder' => implode('/', array_slice($segments, 1)),
        ]);
    }

    /**
     * Reuse the navigation label when the slug is a known nav entry, so the
     * heading reads "Programs" rather than "programs". Detail pages
     * (/en/blog/some-slug) fall back to the slug itself.
     */
    private function label(string $slug): string
    {
        foreach (SiteNav::entries() as $entry) {
            if (($entry['href'] ?? null) === $slug) {
                return __('nav.'.$entry['key']);
            }

            foreach ($entry['children'] as $child) {
                if ($child['href'] === $slug) {
                    return __('nav.'.$child['key']);
                }
            }
        }

        return ucfirst(str_replace('-', ' ', $slug));
    }
}
