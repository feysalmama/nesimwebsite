<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\Testimonial;
use Illuminate\Support\Str;

/**
 * app/admin/(protected)/testimonials/page.tsx, ported.
 *
 * Mixed-language module, as it was in React: the person's name stays one string
 * while their role and their quote are per-language.
 *
 * Left at parity: `rating` is a column the React form never exposed.
 */
final class TestimonialSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Testimonials';
    }

    public function model(): string
    {
        return Testimonial::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'role', 'label' => 'Role / relationship (e.g. Program Beneficiary)', 'type' => 'localeText'],
            ['name' => 'quote', 'label' => 'Quote', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'photoUrl', 'label' => 'Photo', 'type' => 'image'],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'quote', 'label' => 'Quote', 'render' => static fn (Testimonial $item) => Str::limit($item->text('quote', 'en'), 60)],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (Testimonial $item) => $item->published ? 'Yes' : 'No'],
        ];
    }
}
