<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use Illuminate\View\View;

/**
 * Port of app/[locale]/insights/page.tsx.
 *
 * Insights is the third value of newspost.category rather than a table of its
 * own, which is why the page renders NewsCard and links to /news/{id}: that is
 * what insights/page.tsx did, passing n.id as the card's slugId. getNews("insight")
 * filtered in the query, and so does this.
 *
 * Not linked from the header — SiteNav has no insights entry, exactly as the NAV
 * array in components/Navbar.tsx did not — but the route existed and the page was
 * reachable by URL, so it stays reachable.
 */
class InsightController extends Controller
{
    public function __invoke(): View
    {
        return view('site.insights', [
            'insights' => NewsPost::published()
                ->where('category', NewsPost::CATEGORY_INSIGHT)
                ->orderByDesc('publishedAt')
                ->get(),
        ]);
    }
}
