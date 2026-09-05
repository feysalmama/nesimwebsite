<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\FaqItem;

/**
 * app/admin/(protected)/faq/page.tsx, ported.
 *
 * Ordered by the hand-maintained `order` column rather than by age, because
 * prisma/schema.prisma gives FaqItem no createdAt at all and the model sets
 * $timestamps = false — the inherited ordering would name a column that is not
 * in the table. It is also what the public page wants: FaqItem::scopePublished()
 * sorts the same way.
 */
final class FaqItemSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'FAQ';
    }

    public function model(): string
    {
        return FaqItem::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'question', 'label' => 'Question', 'type' => 'localeText', 'required' => true],
            ['name' => 'answer', 'label' => 'Answer', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'question', 'label' => 'Question', 'render' => static fn (FaqItem $item) => $item->text('question', 'en')],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (FaqItem $item) => $item->published ? 'Yes' : 'No'],
        ];
    }

    public function orderBy(): string
    {
        return 'order';
    }

    public function orderDirection(): string
    {
        return 'asc';
    }
}
