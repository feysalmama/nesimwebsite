<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\IslamicMessage;
use Illuminate\Support\Str;

/**
 * app/admin/(protected)/islamic-messages/page.tsx, ported.
 *
 * These are the interstitials components/IslamicMessageBreak.tsx picks at random
 * between home-page sections, which is why `active` rather than `published` is
 * the visibility flag.
 *
 * `arabicText` and `reference` are the same in every language and stay plain.
 * `translation` is not: prisma/seed.js writes it through loc(en, am, om) and
 * page.tsx reads it through tl(message.translation, locale), so the Amharic and
 * Afaan Oromoo renderings of a hadith are part of the row. The React page
 * declared it `type: "textarea"`, which is why the column audit found this table
 * MIXED — the seeded rows still hold three languages and any row edited since
 * holds English alone. localeTextarea reads both shapes (LocaleText::parse puts
 * a plain string into `en`) and writes all three back, so an edited row is
 * repaired rather than further flattened.
 *
 * `type` is a NOT NULL String with `@default("message")`, not an enum, so the
 * three choices below are the only thing constraining it. ResourceManager falls
 * back to that default if the editor picks the empty option.
 */
final class IslamicMessageSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Islamic Messages';
    }

    public function model(): string
    {
        return IslamicMessage::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'default' => 'message', 'options' => ['message', 'ayah', 'hadith']],
            ['name' => 'arabicText', 'label' => 'Arabic Text', 'type' => 'textarea'],
            ['name' => 'translation', 'label' => 'Translation', 'type' => 'localeTextarea', 'required' => true],
            ['name' => 'reference', 'label' => 'Reference (e.g. Surah 2:255)', 'type' => 'text'],
            ['name' => 'backgroundImage', 'label' => 'Background Image', 'type' => 'image'],
            ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'default' => true],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'type', 'label' => 'Type'],
            // `.slice(0, 60) + "..."` in the React table, on the English text
            // rather than on the encoded document.
            ['key' => 'translation', 'label' => 'Translation', 'render' => static fn (IslamicMessage $item) => Str::limit($item->text('translation', 'en'), 60)],
            ['key' => 'reference', 'label' => 'Reference'],
            ['key' => 'active', 'label' => 'Active', 'render' => static fn (IslamicMessage $item) => $item->active ? 'Yes' : 'No'],
        ];
    }
}
