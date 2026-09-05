<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\Project;

/**
 * app/admin/(protected)/projects/page.tsx, ported.
 *
 * One addition to the React field list: `slug`. That page declared no slug
 * field, lib/slug.ts was never imported by anything, and getProjectBySlug()
 * resolves through findUnique({ where: { slug } }) — so every project created in
 * the CMS stored slug = NULL and its detail page could never be reached. The
 * field is optional and slugBase() fills it from the English title when it is
 * left blank, which keeps the form as short as it was.
 *
 * Left at parity, and worth knowing about: categoryId, startDate, endDate,
 * featured, seoTitle and seoDescription are columns the React form never
 * exposed. Nothing here writes them, so existing values are untouched.
 */
final class ProjectSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Projects';
    }

    public function model(): string
    {
        return Project::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'localeText', 'required' => true],
            ['name' => 'slug', 'label' => 'Slug (leave blank to generate)', 'type' => 'text'],
            ['name' => 'summary', 'label' => 'Summary', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'body', 'label' => 'Body', 'type' => 'localeTextarea'],
            ['name' => 'location', 'label' => 'Location', 'type' => 'text'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'default' => 'ongoing', 'options' => ['ongoing', 'completed']],
            ['name' => 'imageUrl', 'label' => 'Image', 'type' => 'image'],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'render' => static fn (Project $item) => $item->text('title', 'en')],
            ['key' => 'location', 'label' => 'Location'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (Project $item) => $item->published ? 'Yes' : 'No'],
        ];
    }

    public function slugBase(): string
    {
        return 'title';
    }
}
