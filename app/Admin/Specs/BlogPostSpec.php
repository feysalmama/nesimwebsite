<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\BlogCategory;
use App\Models\BlogPost;

/**
 * app/admin/(protected)/blog-posts/page.tsx, ported.
 *
 * Two changes from the React declaration, both fixing something that was broken
 * rather than adding to it:
 *
 *  - `categoryId` was a select whose choices came from
 *    `optionsUrl: "/api/admin/blog-categories"`, a second round trip per form
 *    open. Here it is a closure over the model, resolved when the form renders.
 *    Picking nothing now writes NULL: the React form sent "", which Prisma
 *    rejected as a foreign key that does not exist and surfaced as the generic
 *    "Failed to save" alert, so a post could not be saved until a category had
 *    been chosen.
 *  - `publishedAt` was `type: "text"` on a DateTime? column, so clearing it sent
 *    "" to Carbon. It is a real date input now.
 *  - `title`, `excerpt` and `body` are three-language columns seeded through
 *    loc(en, am, om) and read publicly through LocaleText, but the React page
 *    declared all three as plain text and so did this port until the column
 *    audit caught it. Saving a post dropped Amharic and Afaan Oromoo. The
 *    category picker had the same problem from the other side: it plucked
 *    blogcategory.name raw, so its options were listed as encoded JSON.
 *
 * The two checkboxes default to unchecked, matching `@default(false)` in
 * prisma/schema.prisma. The React form fell back to `default ?? true` and so
 * opened with both ticked, which published and featured every new post the
 * moment it was saved.
 */
final class BlogPostSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Blog Posts';
    }

    public function model(): string
    {
        return BlogPost::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'localeText', 'required' => true],
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'required' => true],
            ['name' => 'excerpt', 'label' => 'Excerpt', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'body', 'label' => 'Body', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'coverUrl', 'label' => 'Cover Image', 'type' => 'image'],
            [
                'name' => 'categoryId',
                'label' => 'Category',
                'type' => 'select',
                // The English name: blogcategory.name holds all three languages,
                // so plucking the column listed the options as encoded JSON.
                'options' => static fn () => BlogCategory::query()->orderBy('name')->get()
                    ->mapWithKeys(static fn (BlogCategory $category) => [$category->id => $category->text('name', 'en')])
                    ->all(),
            ],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => false],
            ['name' => 'featured', 'label' => 'Featured', 'type' => 'checkbox', 'default' => false],
            ['name' => 'publishedAt', 'label' => 'Published At', 'type' => 'date'],
            ['name' => 'seoTitle', 'label' => 'SEO Title', 'type' => 'text'],
            ['name' => 'seoDescription', 'label' => 'SEO Description', 'type' => 'textarea'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'render' => static fn (BlogPost $item) => $item->text('title', 'en')],
            ['key' => 'category', 'label' => 'Category', 'render' => static fn (BlogPost $item) => $item->category?->text('name', 'en') ?: '—'],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (BlogPost $item) => $item->published ? 'Yes' : 'No'],
            ['key' => 'featured', 'label' => 'Featured', 'render' => static fn (BlogPost $item) => $item->featured ? 'Yes' : 'No'],
        ];
    }

    /** The table shows the category name, so it has to come back in one query. */
    public function with(): array
    {
        return ['category'];
    }

    public function rules(?string $editingId = null): array
    {
        return $this->uniqueSlug($editingId);
    }
}
