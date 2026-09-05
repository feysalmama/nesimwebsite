<?php

namespace App\Admin\Specs;

use App\Models\ResourceCategory;

/** app/admin/(protected)/resource-categories/page.tsx, ported. */
final class ResourceCategorySpec extends TaxonomySpec
{
    public function title(): string
    {
        return 'Resource Categories';
    }

    public function model(): string
    {
        return ResourceCategory::class;
    }
}
