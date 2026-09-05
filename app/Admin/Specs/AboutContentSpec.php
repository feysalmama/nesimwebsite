<?php

namespace App\Admin\Specs;

use App\Admin\SingletonSpec;
use App\Models\AboutContent;

/**
 * app/admin/(protected)/about-content/page.tsx, ported.
 *
 * Four sections, one row, no list. The hero section is the only one the React
 * page laid out in two columns, so it is the only one carrying 'layout' =>
 * 'grid'. No field declares a span any more: a locale field is always full
 * width, and the hero image takes the half that is left beside it.
 *
 * One real change: timelineData was a bare textarea holding JSON, and the PUT
 * wrote whatever was typed. An editor who dropped a bracket saved it, and
 * AboutController::timeline() — like the React page's safeParse before it —
 * decoded it to an empty array, so the whole "Our History" section quietly
 * vanished with nothing anywhere to say why. The `json` type validates it before
 * the write now.
 *
 * The other, and the larger one: all seven text columns hold three languages.
 * The seed writes each through loc(en, am, om), and both public pages read them
 * through BaseModel::text() — home.blade.php's `$about->text('heroTitle')` and
 * about.blade.php's `$about?->text('heroSubtitle')`. The React page bound one
 * <input> straight over the column, so Hero Title opened showing
 * {"en":"…","am":"…","om":"…"} and the first keystroke replaced the whole
 * document with one plain string — two of the site's three languages gone, with
 * a "About content saved" toast to say it had worked. They are locale fields
 * here: three boxes each, and a taller page than the React one, which is the
 * trade for the Amharic and Afaan Oromoo About page surviving a save.
 */
final class AboutContentSpec extends SingletonSpec
{
    public function title(): string
    {
        return 'About Page Content';
    }

    public function subtitle(): string
    {
        return 'Manage the content displayed on the About page';
    }

    public function model(): string
    {
        return AboutContent::class;
    }

    public function rowId(): string
    {
        return AboutContent::SINGLETON_ID;
    }

    public function sections(): array
    {
        return [
            [
                'title' => 'Hero Section',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'heroTitle', 'label' => 'Hero Title', 'type' => 'localeText'],
                    ['name' => 'heroImageUrl', 'label' => 'Hero Image', 'type' => 'image'],
                    ['name' => 'heroSubtitle', 'label' => 'Hero Subtitle', 'type' => 'localeText'],
                ],
            ],
            [
                'title' => 'Our Story',
                'fields' => [
                    ['name' => 'storyTitle', 'label' => 'Story Title', 'type' => 'localeText'],
                    ['name' => 'storyBody', 'label' => 'Story Body', 'type' => 'localeTextarea', 'rows' => 6],
                    ['name' => 'storyImageUrl', 'label' => 'Story Image', 'type' => 'image'],
                ],
            ],
            [
                'title' => 'Mission, Vision & Values',
                'fields' => [
                    ['name' => 'missionText', 'label' => 'Mission', 'type' => 'localeTextarea', 'rows' => 4],
                    ['name' => 'visionText', 'label' => 'Vision', 'type' => 'localeTextarea', 'rows' => 4],
                    ['name' => 'valuesText', 'label' => 'Values', 'type' => 'localeTextarea', 'rows' => 4],
                ],
            ],
            [
                'title' => 'Timeline',
                'fields' => [
                    [
                        'name' => 'timelineData',
                        'label' => 'Timeline Data (JSON)',
                        'type' => 'json',
                        'rows' => 5,
                        'placeholder' => '[{"year":"2020","title":"Founded","description":"..."}]',
                    ],
                ],
            ],
        ];
    }
}
