<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\NewsPost;

/**
 * app/admin/(protected)/news/page.tsx, ported. Heading text included, since the
 * sidebar says "News" and the page said "News & Media / Insights".
 *
 * Gains an optional `slug` for the same reason projects does: the React form
 * had none, lib/slug.ts was dead code, and getNewsPostBySlug() resolves through
 * findUnique({ where: { slug } }), so every post created here was saved with
 * slug = NULL and its detail page could not be reached.
 *
 * Note on `category`: it is a plain String column holding news/media/insight,
 * which is what this form edits. The separate `newscategory` table behind the
 * News Categories module and the NewsPost.newsCategoryId foreign key were never
 * wired to anything in the Next.js admin either — the two are unrelated, and
 * this port keeps them that way rather than guessing at a link.
 */
final class NewsPostSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'News & Media / Insights';
    }

    public function model(): string
    {
        return NewsPost::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'localeText', 'required' => true],
            ['name' => 'slug', 'label' => 'Slug (leave blank to generate)', 'type' => 'text'],
            ['name' => 'excerpt', 'label' => 'Excerpt', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'body', 'label' => 'Full article', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'coverUrl', 'label' => 'Cover Image', 'type' => 'image'],
            ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'default' => 'news', 'options' => ['news', 'media', 'insight']],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'render' => static fn (NewsPost $item) => $item->text('title', 'en')],
            ['key' => 'category', 'label' => 'Category'],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (NewsPost $item) => $item->published ? 'Yes' : 'No'],
        ];
    }

    public function slugBase(): string
    {
        return 'title';
    }
}
