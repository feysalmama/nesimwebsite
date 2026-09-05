<?php

namespace App\Admin\Specs;

use App\Models\BlogCategory;

/** app/admin/(protected)/blog-categories/page.tsx, ported. */
final class BlogCategorySpec extends TaxonomySpec
{
    public function title(): string
    {
        return 'Blog Categories';
    }

    public function model(): string
    {
        return BlogCategory::class;
    }
}
