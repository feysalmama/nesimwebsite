<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\AboutContent;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\View\View;

/**
 * Port of app/[locale]/about/page.tsx.
 *
 * `$settings` for the navbar/footer is supplied by the view composer registered
 * in AppServiceProvider, not passed from here.
 */
class AboutController extends Controller
{
    /**
     * MISSION / VISION / VALUES. Each entry's text comes from the aboutcontent
     * singleton, with the same literal fallback the React page carried for a
     * fresh or unseeded database.
     */
    private const PILLARS = [
        [
            'icon' => '🎯',
            'titleKey' => 'about.mission',
            'field' => 'missionText',
            'fallback' => 'To expand access to quality education and sustainable development opportunities for underserved communities across Ethiopia.',
        ],
        [
            'icon' => '🌍',
            'titleKey' => 'about.vision',
            'field' => 'visionText',
            'fallback' => 'A generation empowered by education, capable of leading resilient, self-reliant communities.',
        ],
        [
            'icon' => '🌱',
            'titleKey' => 'about.values',
            'field' => 'valuesText',
            'fallback' => 'Integrity, community ownership, equity, and measurable impact guide every program we run.',
        ],
    ];

    public function __invoke(): View
    {
        $about = AboutContent::find('about-content');

        $galleries = Gallery::published()
            ->with(['images' => fn ($q) => $q->orderBy('order')])
            ->orderByDesc('eventDate')
            ->get();

        return view('site.about', [
            'about' => $about,
            'team' => TeamMember::published()->get(),
            'services' => Service::published()->limit(6)->get(),
            'testimonials' => Testimonial::published()->limit(4)->get(),
            'timeline' => $this->timeline($about?->timelineData),
            'pillars' => $this->pillars($about),

            // galleries.flatMap(g => g.images.map(...)).slice(0, 8) — each tile
            // keeps the parent gallery's title as its alt text.
            'galleryImages' => $galleries
                ->flatMap(fn ($g) => $g->images->map(fn ($img) => [
                    'imageUrl' => $img->imageUrl,
                    'galleryTitle' => $g->text('title'),
                ]))
                ->take(8)
                ->values()
                ->all(),
        ]);
    }

    /**
     * The React page wrapped JSON.parse in an empty try/catch, so malformed
     * timelineData rendered nothing rather than erroring. lt_json() has the same
     * tolerance; the array_is_list guard matches `Array.isArray(parsed) ? parsed : []`.
     *
     * Each entry's title and description are locale maps, not the plain strings
     * the React page's type declared — `{item.title}` would have thrown on a
     * well-formed document, and only escaped doing so because the column was
     * truncated at 191 characters and never parsed at all. They are resolved
     * here, which is where this controller's other values are resolved too.
     */
    private function timeline(?string $raw): array
    {
        $parsed = lt_json($raw);

        if (! array_is_list($parsed)) {
            return [];
        }

        $timeline = [];

        foreach ($parsed as $item) {
            if (! is_array($item)) {
                continue;
            }

            $timeline[] = [
                'year' => lt_pick($item['year'] ?? null),
                'title' => lt_pick($item['title'] ?? null),
                'description' => lt_pick($item['description'] ?? null),
            ];
        }

        return $timeline;
    }

    /** Resolve the pillar text against the active locale, falling back per entry. */
    private function pillars(?AboutContent $about): array
    {
        return array_map(fn (array $pillar) => [
            'icon' => $pillar['icon'],
            'title' => __($pillar['titleKey']),
            'text' => $about?->text($pillar['field']) ?: $pillar['fallback'],
        ], self::PILLARS);
    }
}
