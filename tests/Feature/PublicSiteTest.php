<?php

namespace Tests\Feature;

use App\Models\AboutContent;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\LandingContent;
use App\Models\NewsPost;
use App\Models\Project;
use App\Models\Service;
use App\Support\LocaleText;
use App\Support\SiteNav;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The public side of the port: routing, locale prefixes, and that every page
 * the site links to renders from the database in all three languages.
 *
 * Replaces the skeleton's ExampleTest, which asserted that `/` answers 200.
 * It does not — routes/web.php redirects it into the default locale, exactly as
 * next-intl's localePrefix: "always" did, and every URL already indexed by a
 * search engine depends on that staying true.
 *
 * The five submission forms are in SiteFormTest, which needs to write rows.
 *
 * DatabaseTransactions, never RefreshDatabase: there are no migrations, the
 * schema is the one Prisma wrote and the rows are live. See phpunit.xml.
 */
class PublicSiteTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array<string, array<int, string>>
     */
    public static function locales(): array
    {
        return array_combine(
            LocaleText::LOCALES,
            array_map(static fn (string $locale) => [$locale], LocaleText::LOCALES),
        );
    }

    /* ── Locale prefixes ──────────────────────────────────────────────────── */

    public function test_the_root_redirects_into_the_default_locale(): void
    {
        $this->get('/')->assertRedirect('/'.LocaleText::DEFAULT_LOCALE);
    }

    #[DataProvider('locales')]
    public function test_every_locale_serves_the_homepage_under_its_own_prefix(string $locale): void
    {
        $this->get('/'.$locale)
            ->assertOk()
            /*
             * Asserted on the full opening tag rather than on `lang="am"` alone:
             * the language switcher prints one of those per locale on every
             * page, so the short form would pass on the wrong element.
             */
            ->assertSee('<html lang="'.$locale.'"', false);
    }

    public function test_an_unprefixed_public_path_is_redirected_into_the_default_locale(): void
    {
        $this->get('/about')->assertRedirect('/'.LocaleText::DEFAULT_LOCALE.'/about');
    }

    /*
     * A path with a dot is a file request. Redirecting one into the locale
     * prefix is the bug that broke next/image in the Next.js build, so the
     * fallback refuses it before it ever considers a redirect.
     */
    public function test_a_path_containing_a_dot_is_neither_redirected_nor_rendered(): void
    {
        $this->get('/logo.png')->assertNotFound();
        $this->get('/en/logo.png')->assertNotFound();
        $this->get('/en/uploads/missing.jpg')->assertNotFound();
    }

    public function test_a_reserved_prefix_is_left_to_404(): void
    {
        // /admin/{slug} is constrained to the registered specs; anything else
        // must not be redirected into /en/admin/... by the public fallback.
        $this->get('/admin/not-a-module')->assertNotFound();
    }

    /* ── Every ported public page ────────────────────────────────────────── */

    #[DataProvider('locales')]
    public function test_the_about_page_renders_in_every_locale(string $locale): void
    {
        /*
         * The About page reads aboutcontent.timelineData, the column whose
         * Amharic and Afaan Oromoo text was truncated by the original varchar
         * limit and then restored. A 500 here is what that looked like.
         */
        $this->get('/'.$locale.'/about')->assertOk();
    }

    /**
     * The end-to-end proof that the columns the CMS edits are the columns the
     * public pages resolve: one three-language document written to
     * aboutcontent.heroTitle has to arrive as three different headings under
     * three different prefixes. A 200 in every locale would pass while all three
     * pages showed the same English, which is what a raw column read does.
     */
    public function test_the_about_page_serves_the_language_its_prefix_asks_for(): void
    {
        $row = AboutContent::query()->updateOrCreate(['id' => AboutContent::SINGLETON_ID]);

        $row->heroTitle = LocaleText::encode('Zz English Heading', 'Zz አማርኛ ርዕስ', 'Zz Oduu Ajandaa');
        $row->heroSubtitle = LocaleText::encode('Zz English Subtitle', 'Zz አማርኛ ንዑስ', 'Zz Afaan Oromoo');
        $row->save();

        $this->get('/en/about')->assertOk()
            ->assertSee('Zz English Heading')->assertSee('Zz English Subtitle');
        $this->get('/am/about')->assertOk()
            ->assertSee('Zz አማርኛ ርዕስ')->assertSee('Zz አማርኛ ንዑስ');
        $this->get('/om/about')->assertOk()
            ->assertSee('Zz Oduu Ajandaa')->assertSee('Zz Afaan Oromoo');

        /*
         * Blanked in all three languages, the subtitle paragraph is gone rather
         * than empty. The column still holds {"en":"","am":"","om":""}, which is
         * a truthy string, so about.blade.php has to test the resolved text and
         * not the column. That class list is the paragraph's and appears nowhere
         * else on the page.
         */
        $row->heroSubtitle = LocaleText::encode('', '', '');
        $row->save();

        $this->get('/en/about')->assertOk()
            ->assertDontSee('mt-4 max-w-xl text-[15px] leading-relaxed text-white/85', false);
    }

    /**
     * The public pages, as paths under the locale prefix.
     *
     * @return array<string, array<int, string>>
     */
    public static function publicPages(): array
    {
        $paths = [
            '', 'about', 'leadership', 'programs', 'projects', 'services', 'news',
            'blog', 'gallery', 'resources', 'testimonials', 'insights', 'faq',
            'impact', 'contact', 'donate', 'membership', 'volunteer', 'register',
        ];

        $cases = [];

        foreach ($paths as $path) {
            foreach (LocaleText::LOCALES as $locale) {
                $cases['/'.$locale.'/'.$path] = [$locale, $path];
            }
        }

        return $cases;
    }

    /**
     * The whole public site answers, in all three languages.
     *
     * This is the test PlaceholderController existed to keep from failing: every
     * one of these paths used to match a catch-all route and render "This page
     * has not been ported yet" with a 200, so a green suite said nothing about
     * whether the page behind the URL was real. Each now has its own route and
     * its own view, reading the same tables the Next.js app read.
     *
     * The lang attribute is asserted beside the status because a page can render
     * 200 while serving the wrong language, and SetLocale is what carries the
     * prefix into app()->getLocale().
     */
    #[DataProvider('publicPages')]
    public function test_every_public_page_renders_in_every_locale(string $locale, string $path): void
    {
        $this->get(locale_path($path, $locale))
            ->assertOk()
            ->assertSee('<html lang="'.$locale.'"', false);
    }

    /**
     * The five detail routes, against a row that exists.
     *
     * A list page rendering proves the index query works and says nothing about
     * the show() beside it, which takes a slug or a cuid, has to find the row and
     * then resolve its locale columns. Both halves of each pair are checked.
     *
     * It is also the only test that can catch the parameter-ordering bug in
     * App\Http\Controllers\Controller::callAction(). The {locale} prefix is a
     * route parameter, and the dispatcher spreads those positionally, so every
     * one of these methods was handed the locale instead of its own slug - a
     * silent 404 that the index pages, which take no parameters, could never
     * show.
     */
    public function test_every_detail_page_renders_for_a_row_that_exists(): void
    {
        $targets = [
            'blog' => BlogPost::published()->value('slug'),
            'services' => Service::published()->value('slug'),
            'projects' => Project::published()->value('id'),
            'news' => NewsPost::published()->value('id'),
            'gallery' => Gallery::published()->value('id'),
        ];

        foreach ($targets as $section => $key) {
            // Not a skip: with no published row there is nothing to request and
            // the section would be silently untested.
            $this->assertNotNull($key, 'no published '.$section.' row to request a detail page for');

            $url = locale_path($section.'/'.$key);

            $this->assertSame(
                200,
                $this->get($url)->getStatusCode(),
                $url.' did not render',
            );
        }
    }

    /**
     * And 404 for a row that does not exist, rather than rendering an empty
     * shell. firstOrFail() in each controller is what does it; this pins it.
     */
    public function test_a_detail_page_for_a_missing_row_is_not_found(): void
    {
        $this->get('/en/blog/zz-no-such-post')->assertNotFound();
        $this->get('/en/services/zz-no-such-service')->assertNotFound();
        $this->get('/en/projects/zznosuchproject')->assertNotFound();
        $this->get('/en/news/zznosuchnews')->assertNotFound();
        $this->get('/en/gallery/zznosuchgallery')->assertNotFound();
    }

    /**
     * An unknown locale-prefixed path 404s rather than redirecting.
     *
     * The catch-all that absorbed these is gone, so they now reach the global
     * fallback - whose entire job is to add a locale prefix. Without the guard
     * this pins, /en/nothing redirected to /en/en/nothing, which prefixed again
     * and looped until the browser gave up.
     */
    public function test_an_unknown_locale_prefixed_path_is_not_found(): void
    {
        foreach (LocaleText::LOCALES as $locale) {
            $this->get('/'.$locale.'/zz-not-a-page')->assertNotFound();
        }
    }

    /* ── Navigation labels and the language files behind them ─────────────── */

    /**
     * lang/{locale}.json were carried over from next-intl's messages/*.json,
     * where the nesting *is* the lookup mechanism. Laravel resolves a JSON line
     * with a plain array index - Translator::get() reads
     * `$this->loaded['*']['*'][$locale][$key]`, under a comment saying JSON lines
     * "are only one level deep so we do not need to do any fancy searching
     * through it" - so a nested document holds no entry literally keyed
     * "nav.home" and __() hands the key back. It then falls through to the PHP
     * path, lang/en/nav.php, which does not exist either. The header printed
     * "nav.home" on sixteen of its nineteen links.
     *
     * Resolved through __() rather than read out of the HTML, because a raw key
     * on screen is the entire bug and this is the shortest distance to it.
     */
    public function test_every_navigation_label_resolves_in_every_locale(): void
    {
        $keys = ['nav.overview', 'nav.donate', 'footer.explore', 'footer.involved'];

        foreach (SiteNav::entries() as $entry) {
            $keys[] = 'nav.'.$entry['key'];

            foreach ($entry['children'] as $child) {
                $keys[] = 'nav.'.$child['key'];
            }
        }

        foreach (LocaleText::LOCALES as $locale) {
            foreach (array_unique($keys) as $key) {
                $line = __($key, [], $locale);

                $this->assertNotSame(
                    $key,
                    $line,
                    $locale.'.json returned "'.$key.'" unchanged, so it is not flat enough to be looked up',
                );

                $this->assertNotSame('', $line, $locale.'.json has an empty "'.$key.'"');
            }
        }

        /*
         * And the three files are not simply three copies of the English one,
         * which would satisfy every assertion above.
         */
        $home = array_map(
            static fn (string $locale) => __('nav.home', [], $locale),
            LocaleText::LOCALES,
        );

        $this->assertCount(
            count(LocaleText::LOCALES),
            array_unique($home),
            'nav.home reads the same in more than one locale: '.implode(' / ', $home),
        );
    }

    /**
     * The shape itself rather than only the outcome. A nested object anywhere in
     * these files is unreachable by __(), and one added later by hand - the
     * natural instinct for anyone who has only ever used next-intl or a PHP
     * language directory - would silently reintroduce raw keys on whatever page
     * reads it.
     */
    public function test_the_language_files_hold_no_nested_objects(): void
    {
        foreach (LocaleText::LOCALES as $locale) {
            $lines = json_decode((string) file_get_contents(lang_path($locale.'.json')), true);

            $this->assertIsArray($lines, $locale.'.json is not valid JSON');
            $this->assertNotSame([], $lines, $locale.'.json is empty');

            foreach ($lines as $key => $value) {
                $this->assertIsString(
                    $value,
                    $locale.'.json holds a nested object at "'.$key.'", which __() can never reach',
                );
            }
        }
    }

    /**
     * The hamburger has to reach everything the header declares.
     *
     * The links were never missing from the markup - what hid them was four
     * groups that started collapsed, so twelve of the nineteen destinations sat
     * behind four further taps that nothing on screen advertised as taps, and
     * whose labels read "nav.about" and "nav.media". navbar.blade.php renders the
     * groups open now, and this pins both halves: the destinations, and the state
     * that makes them visible rather than merely present.
     */
    public function test_the_mobile_menu_reaches_every_destination_the_header_declares(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $start = strpos($html, 'id="mobile-nav"');

        $this->assertNotFalse($start, 'the slide-in panel is not in the response');

        // <main> follows the navbar partial in layouts/site.blade.php, so it is
        // what bounds the panel now that the panel ends the partial instead of
        // sitting inside the header. See the next test for why it moved.
        $end = strpos($html, '<main', $start);

        $this->assertNotFalse($end, 'the panel is not followed by the page body');

        $panel = substr($html, $start, $end - $start);

        foreach (SiteNav::entries() as $entry) {
            // A group with no landing page of its own - "Media" - has no href.
            $hrefs = $entry['href'] === null ? [] : [locale_path($entry['href'], 'en')];

            foreach ($entry['children'] as $child) {
                $hrefs[] = locale_path($child['href'], 'en');
            }

            foreach ($hrefs as $href) {
                $this->assertStringContainsString(
                    'href="'.$href.'"',
                    $panel,
                    $href.' is not reachable from the hamburger menu',
                );
            }
        }

        $toggles = substr_count($panel, 'data-accordion-toggle');

        $this->assertGreaterThan(0, $toggles, 'the panel has no groups left to check');

        /*
         * aria-expanded rather than the .is-open class, because the attribute is
         * what a screen reader reports and the class is what the stylesheet keys
         * on - if they ever disagree the menu is broken for one of the two.
         */
        $this->assertSame(
            $toggles,
            substr_count($panel, 'data-accordion-toggle aria-expanded="true"'),
            'a group in the hamburger menu renders collapsed, hiding its links',
        );

        $this->assertSame(
            $toggles,
            substr_count($panel, '<div class="is-open">'),
            'a group wrapper is missing the class that expands its body',
        );
    }

    /**
     * The panel and its backdrop have to be rendered outside the <header>.
     *
     * The header carries backdrop-blur-sm, and a backdrop-filter other than none
     * makes an element the containing block for its fixed descendants as well as
     * a stacking context. Rendered inside it, the panel's `fixed right-0 top-0
     * h-full` resolved against the header box instead of the viewport, so it came
     * out 280px wide by roughly the height of the bar and overflow-y-auto clipped
     * the whole link list below the logo row: the hamburger opened a blank white
     * sliver and every destination sat unreachable underneath it.
     *
     * The test above cannot catch that. The links are all present in the markup
     * and correctly spelled - which is exactly why the menu looked fine to every
     * check that read the HTML and was still unusable on a phone. Only the
     * panel's position in the tree decides it.
     */
    public function test_the_mobile_panel_is_rendered_outside_the_header(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $headerEnd = strpos($html, '</header>');
        $backdrop = strpos($html, 'nav-mobile-backdrop');
        $panel = strpos($html, 'id="mobile-nav"');

        $this->assertNotFalse($headerEnd, 'the response has no header');
        $this->assertNotFalse($backdrop, 'the response has no backdrop');
        $this->assertNotFalse($panel, 'the response has no slide-in panel');

        $this->assertGreaterThan(
            $headerEnd,
            $backdrop,
            'the backdrop is inside the header, so it dims the header strip rather than the page',
        );

        $this->assertGreaterThan(
            $headerEnd,
            $panel,
            'the panel is inside the header, so its fixed positioning resolves against the header box and its links are clipped',
        );

        /*
         * The open flag moved with it. app.css reaches the panel, the backdrop
         * and the hamburger bars through html[data-nav-open='true'], and the bars
         * stayed inside the header, so no selector keyed on the header can drive
         * all three any more.
         */
        $this->assertMatchesRegularExpression(
            '/<html[^>]+data-nav-open=/',
            $html,
            'the open flag is not on <html>, so the stylesheet cannot reach the panel',
        );
    }

    /* ── Admin entry point ────────────────────────────────────────────────── */

    public function test_a_guest_reaches_the_login_page_and_not_the_dashboard(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    /* ── The homepage reads what the CMS writes ───────────────────────────── */

    public function test_a_story_photo_reaches_the_homepage_under_either_key(): void
    {
        /*
         * The React admin wrote each story's photo to `imageUrl` and the React
         * homepage read `photoUrl`, so a photo chosen in the CMS was saved and
         * then never displayed. HomeController::stories() accepts both now; this
         * is the regression test for that, and it fails if the normalisation is
         * ever narrowed back to one spelling.
         */
        $row = LandingContent::query()->findOrFail(LandingContent::SINGLETON_ID);

        $row->storiesItems = json_encode([[
            'name' => 'Zz Photo Story', 'role' => 'Student', 'quote' => 'Zz quote',
            'imageUrl' => '/uploads/zz-story-image.png',
        ]]);
        $row->save();

        $this->get('/en')->assertOk()->assertSee('/uploads/zz-story-image.png', false);

        $row->storiesItems = json_encode([[
            'name' => 'Zz Photo Story', 'role' => 'Student', 'quote' => 'Zz quote',
            'photoUrl' => '/uploads/zz-story-legacy.png',
        ]]);
        $row->save();

        $this->get('/en')->assertOk()->assertSee('/uploads/zz-story-legacy.png', false);
    }

    public function test_the_homepage_falls_back_to_its_hardcoded_sections(): void
    {
        $row = LandingContent::query()->findOrFail(LandingContent::SINGLETON_ID);

        // Emptying every JSON collection forces the HomeController fallbacks,
        // which is the state a fresh install is in before anyone opens the CMS.
        foreach (['communityImages', 'reachRegions', 'processSteps', 'impactStats', 'storiesItems', 'factsItems'] as $column) {
            $row->{$column} = null;
        }

        $row->save();

        $this->get('/en')
            ->assertOk()
            ->assertSee('Tigist Hailu')
            ->assertSee('Addis Ababa')
            ->assertSee('25 million');
    }
}
