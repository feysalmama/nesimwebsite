<?php

namespace App\Admin\Specs;

use App\Models\NewsCategory;

/** app/admin/(protected)/news-categories/page.tsx, ported. */
final class NewsCategorySpec extends TaxonomySpec
{
    public function title(): string
    {
        return 'News Categories';
    }

    public function model(): string
    {
        return NewsCategory::class;
    }
}
