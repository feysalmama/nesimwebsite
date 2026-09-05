<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\Resource;
use App\Models\ResourceCategory;

/**
 * app/admin/(protected)/resources/page.tsx, ported.
 *
 * Named DownloadableResourceSpec rather than ResourceSpec: App\Admin\ResourceSpec
 * is the abstract base every table module extends, and two classes a `use`
 * statement apart with the same name is exactly the collision that produces a
 * fatal "cannot extend itself" at the worst possible moment.
 *
 * `fileUrl` stays a plain text field, as it was in React. Admin\UploadController
 * accepts images and video only — the same list the Next.js route had — so there
 * is no upload widget to offer for a PDF. Pointing it at a Media Library URL or
 * an external one both work.
 *
 * `publishedAt` becomes a date input for the reason given in GallerySpec.
 *
 * `title` and `description` are three-language columns and the category picker
 * reads one: resourcecategory.name holds all three too, so plucking the column
 * raw listed the options as encoded JSON. Both are corrected here.
 */
final class DownloadableResourceSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Resources';
    }

    public function model(): string
    {
        return Resource::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'localeText', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'localeTextarea'],
            ['name' => 'fileUrl', 'label' => 'File URL', 'type' => 'text', 'required' => true],
            ['name' => 'coverImage', 'label' => 'Cover Image', 'type' => 'image'],
            ['name' => 'fileType', 'label' => 'File Type (pdf, doc, etc.)', 'type' => 'text'],
            [
                'name' => 'categoryId',
                'label' => 'Category',
                'type' => 'select',
                'options' => static fn () => ResourceCategory::query()->orderBy('name')->get()
                    ->mapWithKeys(static fn (ResourceCategory $category) => [$category->id => $category->text('name', 'en')])
                    ->all(),
            ],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
            ['name' => 'publishedAt', 'label' => 'Published At', 'type' => 'date'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'render' => static fn (Resource $item) => $item->text('title', 'en')],
            ['key' => 'category', 'label' => 'Category', 'render' => static fn (Resource $item) => $item->category?->text('name', 'en') ?: '—'],
            ['key' => 'fileType', 'label' => 'Type'],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (Resource $item) => $item->published ? 'Yes' : 'No'],
        ];
    }

    public function with(): array
    {
        return ['category'];
    }
}
