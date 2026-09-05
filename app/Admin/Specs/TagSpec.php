<?php

namespace App\Admin\Specs;

use App\Models\Tag;

/** app/admin/(protected)/tags/page.tsx, ported. */
final class TagSpec extends TaxonomySpec
{
    public function title(): string
    {
        return 'Tags';
    }

    public function model(): string
    {
        return Tag::class;
    }
}
