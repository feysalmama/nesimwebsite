<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\Program;

/**
 * app/admin/(protected)/programs/page.tsx, ported.
 *
 * One of the six modules whose content is per-language: title, summary and body
 * are the three-language JSON columns described in App\Support\LocaleText, so
 * the form shows three inputs each and the table shows the English one, which is
 * what React's `t(item.title, "en")` did.
 */
final class ProgramSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Programs';
    }

    public function model(): string
    {
        return Program::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'localeText', 'required' => true],
            ['name' => 'summary', 'label' => 'Summary', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'body', 'label' => 'Body', 'type' => 'localeTextarea'],
            ['name' => 'icon', 'label' => 'Icon (emoji, e.g. 📚)', 'type' => 'text'],
            ['name' => 'imageUrl', 'label' => 'Image', 'type' => 'image'],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'render' => static fn (Program $item) => $item->text('title', 'en')],
            ['key' => 'order', 'label' => 'Order'],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (Program $item) => $item->published ? 'Yes' : 'No'],
        ];
    }
}
