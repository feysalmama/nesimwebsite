<?php

namespace Tests\Feature;

use App\Models\AboutContent;
use App\Models\LandingContent;
use App\Support\LocaleText;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The public side of the port: routing, locale prefixes and the two pages that
 * read the database.
 *
 * Replaces the skeleton's ExampleTest, which asserted that `/` answers 200.
 * It does not — routes/web.php redirects it into the default locale, exactly as
 * next-intl's localePrefix: "always" did, and every URL already indexed by a
 * search engine depends on that staying true.
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
     * prefix is the bug that broke next/image in the Next.js build, so both the
     * fallback and PlaceholderController refuse it.
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

    /* ── Ported and unported pages ────────────────────────────────────────── */

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

    public function test_an_unported_page_says_so_rather_than_returning_a_404(): void
    {
        $this->get('/en/programs')
            ->assertOk()
            ->assertSee('This page has not been ported yet');
    }

    public function test_a_detail_path_under_an_unported_page_reports_the_requested_slug(): void
    {
        $this->get('/en/blog/some-post-slug')
            ->assertOk()
            ->assertSee('Requested detail')
            ->assertSee('some-post-slug');
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
