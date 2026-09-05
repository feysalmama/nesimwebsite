<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\Service;

/**
 * app/admin/(protected)/services/page.tsx, ported.
 *
 * The React form labelled its slug field "Slug (auto-generated)" and generated
 * nothing — lib/slug.ts existed but no module imported it — so an editor who
 * left it blank got a NOT NULL violation on a column Prisma declared
 * `slug String @unique`. slugBase() below makes the label true.
 *
 * Unlike the slug, this module's three text columns are not single-language:
 * title, summary and body are seeded through the same loc(en, am, om) helper as
 * programs and projects, and both public read paths — home.blade.php and
 * about.blade.php — call $service->text('title'). The React page declared all
 * three `type: "text"`, so editing a service replaced the three-language
 * document with plain English and the Amharic and Afaan Oromoo versions of the
 * card went blank on the next save.
 */
final class ServiceSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Services';
    }

    public function model(): string
    {
        return Service::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'localeText', 'required' => true],
            ['name' => 'slug', 'label' => 'Slug (leave blank to generate)', 'type' => 'text'],
            ['name' => 'summary', 'label' => 'Summary', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'body', 'label' => 'Full Description', 'type' => 'localeTextarea'],
            ['name' => 'imageUrl', 'label' => 'Cover Image', 'type' => 'image'],
            ['name' => 'icon', 'label' => 'Icon', 'type' => 'text'],
            ['name' => 'featured', 'label' => 'Featured', 'type' => 'checkbox', 'default' => false],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'seoTitle', 'label' => 'SEO Title', 'type' => 'text'],
            ['name' => 'seoDescription', 'label' => 'SEO Description', 'type' => 'textarea'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'render' => static fn (Service $item) => $item->text('title', 'en')],
            ['key' => 'featured', 'label' => 'Featured', 'render' => static fn (Service $item) => $item->featured ? 'Yes' : 'No'],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (Service $item) => $item->published ? 'Yes' : 'No'],
            ['key' => 'order', 'label' => 'Order'],
        ];
    }

    public function slugBase(): string
    {
        return 'title';
    }

    public function rules(?string $editingId = null): array
    {
        return $this->uniqueSlug($editingId);
    }
}
