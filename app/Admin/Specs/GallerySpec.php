<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\Gallery;

/**
 * app/admin/(protected)/galleries/page.tsx, ported.
 *
 * `eventDate` was `type: "text"` in React on a DateTime? column, so clearing it
 * sent "" to Prisma and the save failed with the form's generic alert. It is a
 * date input here, and payload() writes null when it is blank.
 *
 * `title` and `description` are three-language columns — seeded through
 * loc(en, am, om), read publicly through $gallery->text('title') in
 * AboutController and the gallery pages — but the React page declared both as
 * plain text, so saving a gallery dropped two of the site's three languages.
 *
 * This module manages the gallery rows — title, cover, date. The images
 * themselves live in `galleryimage`, which the React admin had no screen for
 * either; they were seeded. Adding one means a spec over GalleryImage plus a
 * galleryId select, not a change here.
 */
final class GallerySpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Galleries';
    }

    public function model(): string
    {
        return Gallery::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'localeText', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'localeTextarea'],
            ['name' => 'coverImage', 'label' => 'Cover Image', 'type' => 'image'],
            ['name' => 'eventDate', 'label' => 'Event Date', 'type' => 'date'],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'render' => static fn (Gallery $item) => $item->text('title', 'en')],
            ['key' => 'eventDate', 'label' => 'Event Date', 'render' => static fn (Gallery $item) => $item->eventDate?->format('j M Y') ?? '—'],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (Gallery $item) => $item->published ? 'Yes' : 'No'],
        ];
    }
}
