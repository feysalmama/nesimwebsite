<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use Illuminate\View\View;

/**
 * Port of app/[locale]/news/page.tsx and news/[id]/page.tsx.
 *
 * The React page called getNews() with no filter and then dropped the rows it did
 * not want in the browser: news.filter(n => n.category === "news" ||
 * n.category === "media"). Same result, filtered in the query instead, so an
 * insight article is never fetched only to be thrown away — and so the count the
 * view tests for its empty state is the count that gets rendered.
 */
class NewsController extends Controller
{
    public function index(): View
    {
        return view('site.news.index', [
            'news' => NewsPost::published()
                ->whereIn('category', NewsPost::FEED_CATEGORIES)
                ->orderByDesc('publishedAt')
                ->get(),
        ]);
    }

    /** getNewsPost(id) then notFound() when missing or unpublished. */
    public function show(string $id): View
    {
        $post = NewsPost::findOrFail($id);

        abort_unless($post->published, 404);

        return view('site.news.show', ['post' => $post]);
    }
}
