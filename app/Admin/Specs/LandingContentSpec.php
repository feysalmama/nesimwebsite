<?php

namespace App\Admin\Specs;

use App\Admin\SingletonSpec;
use App\Models\LandingContent;

/**
 * app/admin/(protected)/landing-content/page.tsx, ported.
 *
 * The largest of the four singletons and the reason SingletonSpec declares the
 * two collection types. Six tabs, each with a title, a one-line description of
 * where the section sits on the home page, some flat fields and one repeater
 * over a JSON array column.
 *
 * Two storage shapes are involved and they are not interchangeable:
 *
 *   communityImages  ["url", "url"]              — a list of bare strings
 *   reachRegions     [{"icon":…,"region":…}]     — a list of objects
 *
 * so `imageList` and `repeater` are separate types rather than one type with a
 * flag. HomeController reads both through lt_json() and would silently render
 * nothing if the shape changed, which is what makes getting this right here
 * worth the extra type.
 *
 * The tab order below is the order of the React `tabs` array — community, reach,
 * process, impact, stories, facts — which is not the order the columns appear in
 * the schema.
 */
final class LandingContentSpec extends SingletonSpec
{
    public function title(): string
    {
        return 'Landing Page Content';
    }

    public function subtitle(): string
    {
        return 'Manage the custom sections on the home page';
    }

    public function model(): string
    {
        return LandingContent::class;
    }

    public function rowId(): string
    {
        return LandingContent::SINGLETON_ID;
    }

    public function tabbed(): bool
    {
        return true;
    }

    public function sections(): array
    {
        return [
            [
                'tab' => 'Community',
                'title' => 'Giving Back to Our Communities',
                'description' => 'The photo mosaic section between testimonials and team.',
                'fields' => [
                    ['name' => 'communityTitle', 'label' => 'Title', 'type' => 'text', 'placeholder' => 'Giving Back to Our Communities'],
                    ['name' => 'communitySubtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'rows' => 2, 'placeholder' => 'Every program, every classroom...'],
                    [
                        'name' => 'communityImages',
                        'label' => 'Mosaic Images (up to 6)',
                        'type' => 'imageList',
                        'max' => 6,
                        'hint' => 'First image is the large featured tile. Upload up to 6 images.',
                        'addLabel' => '+ Add Image',
                        'rowLabel' => 'Image',
                    ],
                ],
            ],
            [
                'tab' => 'Our Reach',
                'title' => 'Our Reach Across Ethiopia',
                'description' => 'Region cards section between Impact Stats and Programs.',
                'fields' => [
                    ['name' => 'reachTitle', 'label' => 'Title', 'type' => 'text', 'placeholder' => 'Where We Work'],
                    ['name' => 'reachSubtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'rows' => 2, 'placeholder' => "Active across Ethiopia's regions..."],
                    [
                        'name' => 'reachRegions',
                        'label' => 'Region Cards',
                        'type' => 'repeater',
                        'max' => 12,
                        'hint' => 'Icon can be an emoji. Count is a short label like "12 schools".',
                        'addLabel' => '+ Add Region',
                        'rowLabel' => 'Region',
                        'subfields' => [
                            ['name' => 'icon', 'label' => 'Icon', 'type' => 'text', 'placeholder' => '📍'],
                            ['name' => 'region', 'label' => 'Region Name', 'type' => 'text', 'placeholder' => 'Addis Ababa'],
                            ['name' => 'count', 'label' => 'Count / Label', 'type' => 'text', 'placeholder' => '12 schools'],
                        ],
                    ],
                ],
            ],
            [
                'tab' => 'Our Process',
                'title' => 'How We Do It — Our Approach',
                'description' => 'Step-by-step process section between Programs and Services.',
                'fields' => [
                    ['name' => 'processTitle', 'label' => 'Title', 'type' => 'text', 'placeholder' => 'How We Do It'],
                    ['name' => 'processSubtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'rows' => 2, 'placeholder' => 'Our proven approach to lasting change...'],
                    [
                        'name' => 'processSteps',
                        'label' => 'Process Steps',
                        'type' => 'repeater',
                        'max' => 8,
                        'hint' => 'Icon can be an emoji or short text. Steps render in order with connecting lines.',
                        'addLabel' => '+ Add Step',
                        'rowLabel' => 'Step',
                        'subfields' => [
                            ['name' => 'icon', 'label' => 'Icon', 'type' => 'text', 'placeholder' => '🔍'],
                            ['name' => 'title', 'label' => 'Step Title', 'type' => 'text', 'placeholder' => 'Identify'],
                            ['name' => 'description', 'label' => 'Description', 'type' => 'text', 'placeholder' => 'Partner with local leaders...'],
                        ],
                    ],
                ],
            ],
            [
                'tab' => 'Impact',
                'title' => 'Impact in Action',
                'description' => 'The dark section with stats, quote, and featured image between team and blog.',
                'fields' => [
                    ['name' => 'impactTitle', 'label' => 'Eyebrow / Title', 'type' => 'text', 'placeholder' => 'One Classroom at a Time'],
                    ['name' => 'impactDescription', 'label' => 'Description', 'type' => 'textarea', 'rows' => 3, 'placeholder' => 'When a community gains access to education...'],
                    ['name' => 'impactImageUrl', 'label' => 'Featured Image', 'type' => 'image'],
                    ['name' => 'impactQuote', 'label' => 'Quote Text', 'type' => 'textarea', 'rows' => 2, 'group' => 'Quote Overlay', 'placeholder' => 'Education is not preparation for life...'],
                    ['name' => 'impactQuoteAuthor', 'label' => 'Quote Author', 'type' => 'text', 'group' => 'Quote Overlay', 'placeholder' => 'John Dewey'],
                    [
                        'name' => 'impactStats',
                        'label' => 'Impact Stats',
                        'type' => 'repeater',
                        'max' => 6,
                        'addLabel' => '+ Add Stat',
                        'rowLabel' => 'Stat',
                        // The React version put these two inputs on one line
                        // rather than in a card each; six of them stack up fast.
                        'inline' => true,
                        'subfields' => [
                            ['name' => 'value', 'label' => 'Value', 'type' => 'text', 'placeholder' => '92%'],
                            ['name' => 'label', 'label' => 'Label', 'type' => 'text', 'placeholder' => 'of students advance...'],
                        ],
                    ],
                    ['name' => 'impactFloatingValue', 'label' => 'Value', 'type' => 'text', 'group' => 'Floating Accent Card', 'span' => 'half', 'placeholder' => '12,400+'],
                    ['name' => 'impactFloatingLabel', 'label' => 'Label', 'type' => 'text', 'group' => 'Floating Accent Card', 'span' => 'half', 'placeholder' => 'Lives Changed'],
                    ['name' => 'impactCtaText', 'label' => 'CTA Button Text', 'type' => 'text', 'span' => 'half', 'placeholder' => 'Explore Our Programs'],
                    ['name' => 'impactCtaUrl', 'label' => 'CTA Button URL', 'type' => 'text', 'span' => 'half', 'placeholder' => '/en/programs'],
                ],
                // The React tab had the two CTA inputs side by side at the
                // bottom and everything else full width.
                'layout' => 'mixed',
            ],
            [
                'tab' => 'Stories',
                'title' => 'Stories from the Field',
                'description' => 'Beneficiary quote cards between Services and Projects.',
                'fields' => [
                    ['name' => 'storiesTitle', 'label' => 'Title', 'type' => 'text', 'placeholder' => 'Stories from the Field'],
                    ['name' => 'storiesSubtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'rows' => 2, 'placeholder' => 'Real voices from the communities we serve...'],
                    [
                        'name' => 'storiesItems',
                        'label' => 'Story Cards',
                        'type' => 'repeater',
                        'max' => 6,
                        'addLabel' => '+ Add Story',
                        'rowLabel' => 'Story',
                        'subfields' => [
                            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'span' => 'half', 'placeholder' => 'Amina H.'],
                            ['name' => 'role', 'label' => 'Role / Location', 'type' => 'text', 'span' => 'half', 'placeholder' => 'Student, Grade 8'],
                            ['name' => 'quote', 'label' => 'Quote', 'type' => 'textarea', 'rows' => 2, 'placeholder' => 'Before the program, I had never held a textbook...'],
                            ['name' => 'imageUrl', 'label' => 'Photo', 'type' => 'image'],
                        ],
                    ],
                ],
            ],
            [
                'tab' => 'Facts',
                'title' => 'Did You Know? Education Facts',
                'description' => 'The gradient ribbon with fact cards between blog and news.',
                'fields' => [
                    ['name' => 'factsTitle', 'label' => 'Title', 'type' => 'text', 'placeholder' => 'Education Changes Everything'],
                    ['name' => 'factsSubtitle', 'label' => 'Subtitle', 'type' => 'textarea', 'rows' => 2, 'placeholder' => 'In Ethiopia, every child who enters a classroom...'],
                    [
                        'name' => 'factsItems',
                        'label' => 'Fact Cards',
                        'type' => 'repeater',
                        'max' => 6,
                        'hint' => 'Icon can be an emoji or short text label.',
                        'addLabel' => '+ Add Fact Card',
                        'rowLabel' => 'Card',
                        'subfields' => [
                            ['name' => 'icon', 'label' => 'Icon', 'type' => 'text', 'placeholder' => '📚'],
                            ['name' => 'fact', 'label' => 'Fact', 'type' => 'text', 'placeholder' => '25 million'],
                            ['name' => 'detail', 'label' => 'Detail', 'type' => 'text', 'placeholder' => 'children are out of school...'],
                        ],
                    ],
                    ['name' => 'factsCtaText', 'label' => 'CTA Button Text', 'type' => 'text', 'span' => 'half', 'placeholder' => 'Help Change These Numbers'],
                    ['name' => 'factsCtaUrl', 'label' => 'CTA Button URL', 'type' => 'text', 'span' => 'half', 'placeholder' => '/en/donate'],
                ],
                'layout' => 'mixed',
            ],
        ];
    }
}
