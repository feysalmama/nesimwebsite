<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\AboutContent;
use App\Models\BlogPost;
use App\Models\HeroSlide;
use App\Models\ImpactStat;
use App\Models\IslamicMessage;
use App\Models\LandingContent;
use App\Models\NewsPost;
use App\Models\Partner;
use App\Models\PresidentMessage;
use App\Models\Program;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\View\View;

/**
 * Port of app/[locale]/page.tsx — the 17-section landing page.
 *
 * The React version fired all fourteen queries through Promise.all. Here they run
 * sequentially because Eloquent is synchronous; the filters and orderings are
 * identical to their lib/content.ts counterparts, and the same hardcoded
 * fallbacks are rendered when a table is empty.
 */
class HomeController extends Controller
{
    /**
     * PLACEHOLDER_STATS from page.tsx. Shown when the impactstat table is empty,
     * so a fresh install still has a credible numbers band. `labelFallback` is
     * what tl(label, locale, fallback) fell back to for these rows.
     */
    private const PLACEHOLDER_STATS = [
        ['id' => 's1', 'value' => 12400, 'suffix' => '+', 'label' => 'Students Reached'],
        ['id' => 's2', 'value' => 86, 'suffix' => '', 'label' => 'Schools Supported'],
        ['id' => 's3', 'value' => 340, 'suffix' => '+', 'label' => 'Volunteers Engaged'],
        ['id' => 's4', 'value' => 9, 'suffix' => '', 'label' => 'Regions Active'],
    ];

    /** PLACEHOLDER_PROGRAMS from page.tsx. */
    private const PLACEHOLDER_PROGRAMS = [
        ['title' => 'Foundational Literacy', 'summary' => 'Early-grade reading and numeracy support in underserved schools.', 'icon' => '📚'],
        ['title' => "Girls' Education Access", 'summary' => 'Removing barriers that keep girls out of the classroom.', 'icon' => '🎓'],
        ['title' => 'Community Development', 'summary' => 'Clean water, sanitation, and livelihood training for families.', 'icon' => '🤝'],
    ];

    /** Section 4b: OUR REACH, when landingcontent.reachRegions is empty. */
    private const FALLBACK_REGIONS = [
        ['icon' => '🏔️', 'region' => 'Amhara', 'count' => '2,400+ students'],
        ['icon' => '🌿', 'region' => 'Oromia', 'count' => '3,100+ students'],
        ['icon' => '🏛️', 'region' => 'Addis Ababa', 'count' => '1,800+ students'],
        ['icon' => '🌾', 'region' => 'SNNPR', 'count' => '1,500+ students'],
        ['icon' => '🏜️', 'region' => 'Afar', 'count' => '680+ students'],
        ['icon' => '🌊', 'region' => 'Somali', 'count' => '520+ students'],
        ['icon' => '🏗️', 'region' => 'Tigray', 'count' => '1,200+ students'],
        ['icon' => '🌳', 'region' => 'Benishangul', 'count' => '340+ students'],
    ];

    /** Section 5b: HOW WE DO IT, when landingcontent.processSteps is empty. */
    private const FALLBACK_PROCESS = [
        ['icon' => '🔍', 'title' => 'Identify', 'description' => 'We partner with local leaders to find the communities with the greatest need and highest motivation for change.'],
        ['icon' => '🤝', 'title' => 'Co-Design', 'description' => "Together with families and teachers, we build a tailored education plan that fits each community's unique context."],
        ['icon' => '🏫', 'title' => 'Build & Train', 'description' => 'We construct classrooms, supply materials, and train local educators to deliver quality instruction.'],
        ['icon' => '📈', 'title' => 'Measure & Grow', 'description' => 'Rigorous tracking ensures every student progresses — and every lesson learned scales to the next village.'],
    ];

    /** Section 6b: STORIES FROM THE FIELD, when landingcontent.storiesItems is empty. */
    private const FALLBACK_STORIES = [
        ['name' => 'Tigist Hailu', 'role' => 'Student, Grade 8', 'quote' => "Before Nesim, I walked two hours to the nearest school. Now I learn just minutes from home — and I dream of becoming a doctor.", 'photoUrl' => ''],
        ['name' => 'Daniel Bekele', 'role' => 'Parent & Farmer', 'quote' => "My daughter reads to me every evening. She teaches me what she learns. This program didn't just change her — it changed our whole family.", 'photoUrl' => ''],
        ['name' => 'Almaz Tesfaye', 'role' => 'Community Teacher', 'quote' => "I was the first in my village to finish secondary school. Now I'm back, teaching the next generation. The cycle of learning continues.", 'photoUrl' => ''],
    ];

    /** Section 12: IMPACT IN ACTION grid, when landingcontent.impactStats is empty. */
    private const FALLBACK_IMPACT_GRID = [
        ['value' => '92%', 'label' => 'of students advance to the next grade'],
        ['value' => '3x', 'label' => 'increase in community literacy rates'],
        ['value' => '85%', 'label' => 'of graduates pursue further education'],
        ['value' => '15+', 'label' => 'communities transformed since founding'],
    ];

    /** Section 14: DID YOU KNOW, when landingcontent.factsItems is empty. */
    private const FALLBACK_FACTS = [
        ['icon' => '📚', 'fact' => '25 million', 'detail' => 'children are out of school in sub-Saharan Africa'],
        ['icon' => '🌍', 'fact' => '1 extra year', 'detail' => 'of schooling boosts earnings by 10%'],
        ['icon' => '👥', 'fact' => 'Every girl educated', 'detail' => 'reduces infant mortality by 10%'],
    ];

    /**
     * Section 10: GIVING BACK mosaic. The React version zipped four parallel
     * arrays (images, overlays, captions, spans) by index; assembling them here
     * keeps the view free of six coordinated counters.
     */
    private const MOSAIC = [
        ['overlay' => 'from-forest/70 via-transparent to-transparent', 'caption' => 'Education for Every Child', 'subcaption' => 'Reaching underserved communities across 9 regions', 'span' => 'col-span-2 row-span-2 sm:col-span-2 sm:row-span-2 lg:col-span-3'],
        ['overlay' => 'bg-forest/30', 'caption' => '', 'subcaption' => '', 'span' => 'col-span-1 sm:col-span-1 lg:col-span-1'],
        ['overlay' => 'bg-leaf/20', 'caption' => '', 'subcaption' => '', 'span' => 'col-span-1 sm:col-span-1 lg:col-span-1'],
        ['overlay' => 'bg-sun/15', 'caption' => '', 'subcaption' => '', 'span' => 'col-span-2 sm:col-span-2 lg:col-span-1'],
        ['overlay' => 'bg-forest/20', 'caption' => '', 'subcaption' => '', 'span' => 'col-span-1 lg:col-span-1'],
        ['overlay' => 'bg-gradient-to-r from-forest/50 to-transparent', 'caption' => 'Together We Grow', 'subcaption' => '', 'span' => 'col-span-1 lg:col-span-2'],
    ];

    public function __invoke(): View
    {
        $landing = LandingContent::find('landing-content');

        $programs = Program::published()->limit(3)->get();

        return view('site.home', [
            // Section 1 — HeroSlider renders its own fallback when this is empty.
            'slides' => HeroSlide::active()->get(),

            // Section 2 — only the first active message was ever shown.
            'islamic' => IslamicMessage::active()->first(),

            // Section 3
            'about' => AboutContent::find('about-content'),

            /*
             * Section 4. Note this deliberately does NOT use the model's
             * scopeActive(): getImpactStats() in lib/content.ts is
             * findMany({ orderBy: { order: 'asc' } }) with no `where` at all, so
             * an inactive stat still counted on the homepage. Preserved as-is so
             * the port renders the same numbers as the site it replaces.
             */
            'impactStats' => ImpactStat::query()->orderBy('order')->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'value' => (int) $s->value,
                    'suffix' => (string) ($s->suffix ?? ''),
                    'label' => $s->text('label'),
                ])
                ->all() ?: self::PLACEHOLDER_STATS,

            'landing' => $landing,

            // Sections 4b / 5b / 6b / 12 / 14 read JSON columns off the
            // landingcontent singleton, each with a hardcoded fallback array.
            'reachRegions' => lt_json($landing?->reachRegions) ?: self::FALLBACK_REGIONS,
            'processSteps' => lt_json($landing?->processSteps) ?: self::FALLBACK_PROCESS,
            'storiesItems' => $this->stories(lt_json($landing?->storiesItems)),
            'impactGrid' => lt_json($landing?->impactStats) ?: self::FALLBACK_IMPACT_GRID,
            'factsItems' => lt_json($landing?->factsItems) ?: self::FALLBACK_FACTS,
            'mosaic' => $this->mosaic(lt_json($landing?->communityImages)),

            'programs' => $programs,
            'placeholderPrograms' => $programs->isEmpty() ? self::PLACEHOLDER_PROGRAMS : [],

            'services' => Service::published()->limit(3)->get(),
            'projects' => Project::published()->limit(3)->get(),
            'president' => PresidentMessage::find('president-message'),
            'testimonials' => Testimonial::published()->limit(3)->get(),
            'team' => TeamMember::published()->get(),

            'blogPosts' => BlogPost::published()
                ->with(['category', 'author'])
                ->orderByDesc('publishedAt')
                ->limit(3)
                ->get(),

            'news' => NewsPost::published()
                ->orderByDesc('publishedAt')
                ->limit(3)
                ->get(),

            'partners' => Partner::active()->get(),
        ]);
    }

    /**
     * Normalise the story cards onto the one key the view reads.
     *
     * The React admin page wrote each story's photo to `imageUrl` while the
     * React home page read `photoUrl`, so a photo chosen in the CMS was saved
     * and then never displayed. Both spellings are accepted here, `imageUrl`
     * first because that is the one LandingContentSpec still writes, rather
     * than leaving the view to test two keys per card.
     */
    private function stories(mixed $rows): array
    {
        if (! is_array($rows) || $rows === []) {
            $rows = self::FALLBACK_STORIES;
        }

        return array_map(static function (mixed $story): array {
            $story = is_array($story) ? $story : [];

            $story['photoUrl'] = ($story['imageUrl'] ?? '') ?: ($story['photoUrl'] ?? '');

            return $story;
        }, $rows);
    }

    /**
     * Zip the community images onto the fixed overlay/caption/span track, then
     * cap at six tiles exactly as imgs.slice(0, 6) did. An empty image list
     * falls back to six copies of the default hero, so the grid never collapses.
     */
    private function mosaic(array $images): array
    {
        $images = array_slice($images, 0, 6);

        if ($images === []) {
            $images = array_fill(0, 6, '/hero-default.jpg');
        }

        $tiles = [];

        foreach (self::MOSAIC as $i => $meta) {
            $url = $images[$i] ?? null;

            if ($url === null) {
                break;
            }

            $tiles[] = $meta + ['url' => $url ?: '/hero-default.jpg', 'alt' => 'Community '.($i + 1)];
        }

        return $tiles;
    }
}
