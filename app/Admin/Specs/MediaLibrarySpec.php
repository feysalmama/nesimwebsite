<?php

namespace App\Admin\Specs;

use App\Admin\AdminSpec;
use App\Models\Media;

/**
 * app/admin/(protected)/media/page.tsx, ported.
 *
 * The screen is a grid of files rather than a table of rows, so it fits none of
 * the three spec shapes and gets its own component. What the spec still carries
 * is everything AdminNav needs to treat it like any other module: the slug it
 * registers under, the heading, the table the activity log names, and the roles
 * — requireStaff() on both sides, which is what the two media routes used.
 *
 * Media is the one table the CMS writes to from somewhere other than its own
 * screen: Admin\UploadController inserts a row for every file uploaded anywhere
 * in the panel, which is how this library stays complete without the editor
 * having to register anything.
 */
final class MediaLibrarySpec extends AdminSpec
{
    public function title(): string
    {
        return 'Media Library';
    }

    public function model(): string
    {
        return Media::class;
    }

    public function component(): string
    {
        return 'admin.media-library';
    }
}
