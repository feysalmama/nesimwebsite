<?php

namespace App\Admin\Specs;

use App\Models\ProjectCategory;

/** app/admin/(protected)/project-categories/page.tsx, ported. */
final class ProjectCategorySpec extends TaxonomySpec
{
    public function title(): string
    {
        return 'Project Categories';
    }

    public function model(): string
    {
        return ProjectCategory::class;
    }
}
