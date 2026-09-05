<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\HeroSlide;

/**
 * app/admin/(protected)/hero-slides/page.tsx, ported.
 *
 * The React page was seven fields and three columns handed to ResourceManager.
 * This is the same declaration in PHP, in the same order, with the same
 * required flags and defaults — including the `active` column rendering
 * "Yes"/"No" instead of the raw boolean.
 *
 * One correction. The React page declared title, subtitle and buttonText as
 * plain text, but page.tsx passes all three through tl(slide.title, locale) and
 * hero-slider.blade.php reads them through $slide->text('title'): the slider is
 * the most translated thing on the site. Editing a slide therefore replaced a
 * three-language document with English alone and the Amharic and Afaan Oromoo
 * hero silently fell back to it. They are locale fields here.
 */
final class HeroSlideSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Hero Slides';
    }

    public function model(): string
    {
        return HeroSlide::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'imageUrl', 'label' => 'Background Image', 'type' => 'image', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'type' => 'localeText', 'required' => true],
            ['name' => 'subtitle', 'label' => 'Subtitle', 'type' => 'localeTextarea'],
            ['name' => 'buttonText', 'label' => 'Button Text', 'type' => 'localeText'],
            ['name' => 'buttonUrl', 'label' => 'Button URL', 'type' => 'text'],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title', 'render' => static fn (HeroSlide $slide) => $slide->text('title', 'en')],
            ['key' => 'order', 'label' => 'Order'],
            ['key' => 'active', 'label' => 'Active', 'render' => static fn (HeroSlide $slide) => $slide->active ? 'Yes' : 'No'],
        ];
    }

    /**
     * makeCollectionRoutes(prisma.heroSlide, { order: "asc" }, ...) — the one
     * module on the site that sorted by a hand-maintained column rather than by
     * age, because the slider plays these in sequence.
     */
    public function orderBy(): string
    {
        return 'order';
    }

    public function orderDirection(): string
    {
        return 'asc';
    }
}
