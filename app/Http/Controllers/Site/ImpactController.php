<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ImpactStat;
use App\Models\LandingContent;
use Illuminate\View\View;

/**
 * The page behind the "Impact" link in the header.
 *
 * Not a port, and worth saying so plainly: there is no app/[locale]/impact/
 * directory in the Next.js source, while NAV in components/Navbar.tsx has had
 * { key: "impact", href: "impact" } since the beginning and SiteNav carries it
 * across. On the live Next.js site that link was a 404. The homepage has a
 * section with id="impact" but no route, so there is nothing to be faithful to
 * and this page is built from data the site already holds.
 *
 * Two choices that follow from having no original:
 *
 *  - scopeActive() rather than the homepage's unfiltered query. HomeController
 *    reproduces getImpactStats(), which had no `where` clause, so a stat an
 *    editor switched off still counted there. This page filters, because `active`
 *    is the column's whole purpose and a new page has no legacy to preserve.
 *  - prefix, icon and description are rendered. The homepage counter reads only
 *    value, suffix and label, and those three columns are otherwise written by
 *    the CMS and never shown to anyone.
 */
class ImpactController extends Controller
{
    public function __invoke(): View
    {
        $landing = LandingContent::find(LandingContent::SINGLETON_ID);

        return view('site.impact', [
            'stats' => ImpactStat::active()->get()
                ->map(static fn (ImpactStat $stat) => [
                    'id' => $stat->id,
                    'value' => (int) $stat->value,
                    'prefix' => (string) ($stat->prefix ?? ''),
                    'suffix' => (string) ($stat->suffix ?? ''),
                    'icon' => (string) ($stat->icon ?? ''),
                    'label' => $stat->text('label'),
                    'description' => $stat->text('description'),
                ])
                ->all() ?: ImpactStat::FALLBACK_ROWS,

            /*
             * Rendered only when present. The homepage substitutes a hardcoded
             * list of eight regions when this column is empty, because its
             * section would otherwise be a visibly blank grid inside a designed
             * block; here the section is simply omitted, the way every list page
             * omits its grid and shows one line instead.
             */
            'reachRegions' => lt_json($landing?->reachRegions),
        ]);
    }
}
