<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\BaseModel;

/**
 * The five lookup tables that are nothing but a name and a slug: blog
 * categories, tags, news categories, project categories and resource
 * categories.
 *
 * Their React pages were byte-for-byte the same declaration five times over, so
 * the shared half lives here and each subclass is a title and a model. They
 * share four things that are easy to get wrong individually:
 *
 *  - `name` is a three-language column. Every one of the five is seeded through
 *    the same loc(en, am, om) helper as the content tables and every public read
 *    goes through LocaleText, but all five React pages declared it `type:
 *    "text"` — so saving a category replaced {"en":…,"am":…,"om":…} with plain
 *    English and dropped the other two languages from the picker it feeds.
 *    localeText is what the column actually holds.
 *  - No createdAt column. Prisma declared none of the five with timestamps and
 *    the models set $timestamps = false, so the inherited `createdAt desc`
 *    ordering would be a query against a column that does not exist.
 *  - A slug the editor types, on a column Prisma declared `@unique`. A
 *    duplicate used to come back as a P2002 that lib/crudRoute.ts turned into a
 *    bare 500.
 *  - Alphabetical listing, which is how a picker wants them. Ordering by the raw
 *    column orders by the encoded JSON, and since every value starts with
 *    {"en":" that is ordering by the English name — which is the intent.
 */
abstract class TaxonomySpec extends ResourceSpec
{
    public function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'localeText', 'required' => true],
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'required' => true],
        ];
    }

    public function columns(): array
    {
        return [
            // The English name, as React's `t(item.name, "en")` showed it. Without
            // the render, cell() would print the encoded JSON into the table.
            ['key' => 'name', 'label' => 'Name', 'render' => static fn (BaseModel $item) => $item->text('name', 'en')],
            ['key' => 'slug', 'label' => 'Slug'],
        ];
    }

    public function orderBy(): string
    {
        return 'name';
    }

    public function orderDirection(): string
    {
        return 'asc';
    }

    public function rules(?string $editingId = null): array
    {
        return $this->uniqueSlug($editingId);
    }
}
