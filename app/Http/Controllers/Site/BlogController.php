<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\GlobalSettings;
use Illuminate\View\View;

/**
 * Port of app/[locale]/blog/page.tsx and blog/[slug]/page.tsx.
 *
 * getBlogPosts() and getBlogPostBySlug() both included category, tags and
 * author.name, so both eager load the same three. Without it the listing fires
 * two extra queries per card.
 */
class BlogController extends Controller
{
    /** The relations every blog query included in lib/content.ts. */
    private const WITH = ['category', 'tags', 'author'];

    public function index(): View
    {
        return view('site.blog.index', [
            'posts' => BlogPost::published()
                ->with(self::WITH)
                ->orderByDesc('publishedAt')
                ->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::where('slug', $slug)->with(self::WITH)->firstOrFail();

        abort_unless($post->published, 404);

        return view('site.blog.show', [
            'post' => $post,
            'schema' => $this->articleSchema($post, $slug),
        ]);
    }

    /**
     * articleSchema() from components/StructuredData.tsx, which only the blog
     * detail page emitted — the other four detail pages had no JSON-LD at all.
     *
     * Two deliberate differences. The url was built from a NEXT_PUBLIC_SITE_URL
     * environment variable that defaults to a hardcoded production domain, so a
     * staging deployment told Google it was production; url() uses the host the
     * request actually arrived on. And nulls are stripped, because JSON.stringify
     * drops an undefined property while json_encode keeps a null one, and
     * "image": null is not what the original emitted.
     *
     * @return array<string, mixed>
     */
    private function articleSchema(BlogPost $post, string $slug): array
    {
        $settings = GlobalSettings::current();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->text('title'),
            'description' => $post->text('excerpt') ?: null,
            'url' => url(locale_path('blog/'.$slug)),
            'image' => $post->coverUrl ? url($post->coverUrl) : null,
            'datePublished' => $post->publishedAt?->toIso8601String(),
            'dateModified' => ($post->updatedAt ?: $post->publishedAt)?->toIso8601String(),
            'author' => $post->author?->name
                ? ['@type' => 'Person', 'name' => $post->author->name]
                : null,
            'publisher' => array_filter([
                '@type' => 'Organization',
                'name' => $settings->orgName ?: 'Nesim Foundation',
                'logo' => $settings->logoUrl
                    ? ['@type' => 'ImageObject', 'url' => url($settings->logoUrl)]
                    : null,
            ], static fn ($value) => $value !== null),
        ];

        return array_filter($schema, static fn ($value) => $value !== null);
    }
}
