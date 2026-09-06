<?php

namespace Tests\Feature;

use App\Livewire\Admin\ActivityLogs;
use App\Livewire\Admin\MediaLibrary;
use App\Livewire\Admin\ResourceManager;
use App\Livewire\Admin\SingletonEditor;
use App\Livewire\Admin\SubmissionManager;
use App\Livewire\Admin\UserManager;
use App\Models\AboutContent;
use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\GlobalSettings;
use App\Models\LandingContent;
use App\Models\Media;
use App\Models\Partner;
use App\Models\PresidentMessage;
use App\Models\Service;
use App\Models\User;
use App\Models\VolunteerApplication;
use App\Support\AdminNav;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The CMS, exercised rather than merely requested.
 *
 * smoke-admin.php proves all 33 routes answer 200, which is not the same claim:
 * a panel can render and still refuse to save. These tests drive the four
 * component shapes through Livewire's own test harness — open the form, set the
 * fields, call the method, look at the row — which is how the two defects that
 * survived the route check were found.
 *
 * DatabaseTransactions, never RefreshDatabase: this project has no migrations,
 * the schema is the one Prisma wrote and the rows in it are live. Every write
 * below is rolled back. See the comment in phpunit.xml.
 */
class AdminPanelTest extends TestCase
{
    use DatabaseTransactions;

    /* ── Every route ──────────────────────────────────────────────────────── */

    /**
     * @return array<string, array<int, string>>
     */
    public static function adminSlugs(): array
    {
        $slugs = array_merge(['dashboard'], array_keys(AdminNav::specs()));

        return array_combine($slugs, array_map(static fn (string $slug) => [$slug], $slugs));
    }

    /**
     * A super admin, so the two role-gated modules are reachable.
     */
    #[DataProvider('adminSlugs')]
    public function test_every_admin_route_renders_its_panel(string $slug): void
    {
        $this->actingAs($this->staff());

        $response = $this->get($slug === 'dashboard' ? '/admin' : '/admin/'.$slug);

        $response->assertOk();

        /*
         * "wire:" appears in an admin response only when a Livewire component
         * hydrated: the layout, the sidebar and the topbar are plain Blade, and
         * the dashboard is the one route with no component at all. Without this
         * a page that rendered its heading and then an empty body would pass.
         */
        if ($slug !== 'dashboard') {
            $response->assertSee('wire:', false);
        } else {
            $response->assertSee('Dashboard');
        }
    }

    /* ── Role gates ───────────────────────────────────────────────────────── */

    public function test_activity_logs_is_closed_to_roles_outside_the_two_content_editors(): void
    {
        $this->actingAs($this->staff(User::ROLE_EDITOR))
            ->get('/admin/activity-logs')
            ->assertForbidden();

        $this->actingAs($this->staff(User::ROLE_CONTENT_ADMIN))
            ->get('/admin/activity-logs')
            ->assertOk();
    }

    public function test_staff_users_is_closed_to_everyone_but_a_super_admin(): void
    {
        $this->actingAs($this->staff(User::ROLE_CONTENT_ADMIN))
            ->get('/admin/users')
            ->assertForbidden();
    }

    /* ── ResourceSpec: the 22 table modules ───────────────────────────────── */

    public function test_resource_manager_creates_updates_and_deletes_a_row(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(ResourceManager::class, ['resource' => 'partners'])
            ->call('openCreate')
            ->set('form.name', 'Zz Test Partner')
            ->set('form.websiteUrl', 'https://example.org')
            ->set('form.order', 7)
            ->set('form.active', true)
            ->call('save')
            ->assertHasNoErrors();

        $partner = Partner::query()->where('name', 'Zz Test Partner')->first();

        $this->assertNotNull($partner, 'the row was not written');
        $this->assertSame(7, (int) $partner->order);
        $this->assertTrue((bool) $partner->active);

        Livewire::test(ResourceManager::class, ['resource' => 'partners'])
            ->call('openEdit', (string) $partner->getKey())
            ->assertSet('form.name', 'Zz Test Partner')
            ->set('form.name', 'Zz Renamed Partner')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Zz Renamed Partner', $partner->fresh()->name);

        Livewire::test(ResourceManager::class, ['resource' => 'partners'])
            ->call('delete', (string) $partner->getKey());

        $this->assertNull(Partner::query()->find($partner->getKey()));

        $this->assertDatabaseHas('activitylog', ['entity' => 'Partner', 'action' => 'create']);
        $this->assertDatabaseHas('activitylog', ['entity' => 'Partner', 'action' => 'update']);
        $this->assertDatabaseHas('activitylog', ['entity' => 'Partner', 'action' => 'delete']);
    }

    public function test_resource_manager_refuses_a_required_field_left_blank(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(ResourceManager::class, ['resource' => 'partners'])
            ->call('openCreate')
            ->set('form.name', '')
            ->call('save')
            ->assertHasErrors('form.name');
    }

    public function test_resource_manager_rejects_an_address_that_is_not_an_upload_or_http(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(ResourceManager::class, ['resource' => 'partners'])
            ->call('openCreate')
            ->call('setMediaUrl', 'logoUrl', 'javascript:alert(1)')
            ->assertSet('form.logoUrl', null)
            ->assertSet('uploadError', 'That address was rejected. Upload a file instead.');

        Livewire::test(ResourceManager::class, ['resource' => 'partners'])
            ->call('openCreate')
            ->call('setMediaUrl', 'logoUrl', '/uploads/zz-fine.png')
            ->assertSet('form.logoUrl', '/uploads/zz-fine.png')
            ->assertSet('uploadError', null);
    }

    /**
     * Bank accounts is the one module that is not a port, and the only one whose
     * columns no Prisma schema ever described — a migration wrote them. So the
     * spec's field names have to match it, and only a save can prove that: the
     * panel renders `order` and `active` and reads nothing else, so a misspelt
     * accountNumber would sit undetected until an editor first pressed Save.
     */
    public function test_bank_accounts_writes_every_field_its_spec_declares(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(ResourceManager::class, ['resource' => 'bank-accounts'])
            ->call('openCreate')
            ->set('form.name', 'Zz Test Bank')
            ->set('form.accountNumber', '1000999888777')
            ->set('form.accountName', 'Zz Nesim Foundation')
            ->call('setMediaUrl', 'logoUrl', '/uploads/zz-bank-logo.png')
            ->set('form.order', 3)
            ->set('form.active', true)
            ->call('save')
            ->assertHasNoErrors();

        $bank = BankAccount::query()->where('name', 'Zz Test Bank')->first();

        $this->assertNotNull($bank, 'the row was not written');
        $this->assertSame('1000999888777', $bank->accountNumber);
        $this->assertSame('Zz Nesim Foundation', $bank->accountName);
        $this->assertSame('/uploads/zz-bank-logo.png', $bank->logoUrl);
        $this->assertSame(3, (int) $bank->order);
        $this->assertTrue((bool) $bank->active);

        $this->assertDatabaseHas('activitylog', ['entity' => 'BankAccount', 'action' => 'create']);
    }

    /**
     * The resource side of the locale defect, and the picker that reads from it.
     *
     * TaxonomySpec is the shared abstract behind all five category tables, so
     * this covers five modules at once. BlogPostSpec's category <select> plucked
     * blogcategory.name raw, which listed every option as the encoded document;
     * it decodes the English name now, and so does the post's own table cell.
     */
    public function test_resource_manager_round_trips_a_locale_field_and_decodes_it_in_a_picker(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(ResourceManager::class, ['resource' => 'blog-categories'])
            ->call('openCreate')
            ->set('form.name', [
                'en' => 'Zz Scholarship News',
                'am' => 'Zz የትምህርት ዜና',
                'om' => 'Zz Oduu Barnootaa',
            ])
            ->set('form.slug', 'zz-scholarship-news')
            ->call('save')
            ->assertHasNoErrors();

        $category = BlogCategory::query()->where('slug', 'zz-scholarship-news')->first();

        $this->assertNotNull($category, 'the category was not written');
        $this->assertSame('Zz የትምህርት ዜና', $category->text('name', 'am'));
        $this->assertSame('Zz Oduu Barnootaa', $category->text('name', 'om'));

        // The listing shows the English name, not the column's JSON.
        Livewire::test(ResourceManager::class, ['resource' => 'blog-categories'])
            ->assertSee('Zz Scholarship News');

        // And so does the option the blog post form builds from it.
        Livewire::test(ResourceManager::class, ['resource' => 'blog-posts'])
            ->call('openCreate')
            ->assertSeeHtml('<option value="'.$category->id.'">Zz Scholarship News</option>');

        /*
         * A post saved against that category keeps its own three languages and
         * shows the category decoded in the table cell beside it.
         */
        Livewire::test(ResourceManager::class, ['resource' => 'blog-posts'])
            ->call('openCreate')
            ->set('form.title', ['en' => 'Zz First Post', 'am' => 'Zz የመጀመሪያ ልጥፍ', 'om' => 'Zz kan jalqabaa'])
            ->set('form.slug', 'zz-first-post')
            ->set('form.excerpt', ['en' => 'Zz excerpt', 'am' => 'Zz ማጠቃለያ', 'om' => 'Zz guduunfa'])
            ->set('form.body', ['en' => 'Zz body', 'am' => 'Zz ይዘት', 'om' => 'Zz qabiyyee'])
            ->set('form.categoryId', $category->id)
            ->call('save')
            ->assertHasNoErrors();

        $post = BlogPost::query()->where('slug', 'zz-first-post')->first();

        $this->assertNotNull($post, 'the post was not written');
        $this->assertSame('Zz የመጀመሪያ ልጥፍ', $post->text('title', 'am'));
        $this->assertSame('Zz qabiyyee', $post->text('body', 'om'));

        Livewire::test(ResourceManager::class, ['resource' => 'blog-posts'])
            ->assertSee('Zz First Post')
            ->assertSee('Zz Scholarship News');
    }

    /**
     * A slug generated from a locale field has to be derived from the English
     * half, and that is only knowable from the raw validated value — by the time
     * the payload exists the three languages are one encoded string and the base
     * text would have to be decoded again to read it.
     */
    public function test_resource_manager_generates_a_slug_from_the_english_half_of_a_locale_field(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(ResourceManager::class, ['resource' => 'services'])
            ->call('openCreate')
            ->set('form.title', ['en' => 'Zz Tutoring Programme', 'am' => 'Zz የትምህርት ፕሮግራም', 'om' => 'Zz Sagantaa Barnootaa'])
            ->set('form.summary', ['en' => 'Zz summary', 'am' => 'Zz ማጠቃለያ', 'om' => 'Zz guduunfa'])
            ->call('save')
            ->assertHasNoErrors();

        $service = Service::query()->where('slug', 'zz-tutoring-programme')->first();

        $this->assertNotNull($service, 'no slug was generated from the English title');
        $this->assertSame('Zz የትምህርት ፕሮግራም', $service->text('title', 'am'));
        $this->assertSame('Zz Sagantaa Barnootaa', $service->text('title', 'om'));
    }

    /* ── SubmissionSpec: the four queues ──────────────────────────────────── */

    public function test_submission_manager_accepts_a_declared_status_and_ignores_any_other(): void
    {
        $this->actingAs($this->staff());

        $application = VolunteerApplication::create([
            'name' => 'Zz Volunteer',
            'email' => 'zz.volunteer@example.org',
            'phone' => '+251900000000',
            'city' => 'Addis Ababa',
            'status' => 'new',
        ]);

        // The React PATCH handed any string straight to Prisma, so "banana" was
        // a valid status and the dashboard's where('status','new') count drifted.
        Livewire::test(SubmissionManager::class, ['resource' => 'volunteers'])
            ->call('setStatus', (string) $application->getKey(), 'banana')
            ->assertHasNoErrors();

        $this->assertSame('new', $application->fresh()->status);

        Livewire::test(SubmissionManager::class, ['resource' => 'volunteers'])
            ->call('setStatus', (string) $application->getKey(), 'accepted');

        $this->assertSame('accepted', $application->fresh()->status);

        $this->assertDatabaseHas('activitylog', [
            'entity' => 'VolunteerApplication',
            'action' => 'update',
            'entityId' => (string) $application->getKey(),
        ]);
    }

    /* ── SingletonSpec: the four one-row screens ──────────────────────────── */

    public function test_singleton_editor_saves_settings_and_refuses_a_malformed_colour(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(SingletonEditor::class, ['resource' => 'settings'])
            ->set('form.orgName', 'Zz Test Organization')
            ->set('form.primaryColor', 'not-a-colour')
            ->call('save')
            ->assertHasErrors('form.primaryColor');

        Livewire::test(SingletonEditor::class, ['resource' => 'settings'])
            ->set('form.orgName', 'Zz Test Organization')
            ->set('form.primaryColor', '#123456')
            ->call('save')
            ->assertHasNoErrors();

        $row = GlobalSettings::query()->find(GlobalSettings::SINGLETON_ID);

        $this->assertNotNull($row);
        $this->assertSame('Zz Test Organization', $row->orgName);
        $this->assertSame('#123456', $row->primaryColor);
    }

    public function test_singleton_editor_refuses_json_that_does_not_parse(): void
    {
        $this->actingAs($this->staff());

        /*
         * The React page rendered timelineData as an ordinary textarea and PUT
         * whatever was in it, so an editor who dropped a bracket saved a column
         * the public About page then could not read. This is the rule that stops
         * it, and it is the reason the field type exists.
         */
        Livewire::test(SingletonEditor::class, ['resource' => 'about-content'])
            ->set('form.timelineData', '[{"year": "2015", ')
            ->call('save')
            ->assertHasErrors('form.timelineData');

        Livewire::test(SingletonEditor::class, ['resource' => 'about-content'])
            ->set('form.timelineData', '[{"year": "2015", "title": {"en": "Zz Founded"}, "description": {"en": "Zz"}}]')
            ->call('save')
            ->assertHasNoErrors();

        $saved = AboutContent::query()->find(AboutContent::SINGLETON_ID);

        $this->assertNotNull($saved);
        $this->assertStringContainsString('Zz Founded', (string) $saved->timelineData);
    }

    /**
     * Nine singleton columns hold three languages, and the React pages bound one
     * <input> over each: the box showed the encoded document, and the first
     * keystroke replaced all three languages with a plain string. The save
     * reported success, so the Amharic and Afaan Oromoo site lost its About page
     * text with nothing anywhere to say it had.
     */
    public function test_singleton_editor_round_trips_all_three_languages_of_a_locale_field(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(SingletonEditor::class, ['resource' => 'about-content'])
            ->set('form.heroTitle', [
                'en' => 'Zz English Title',
                'am' => 'Zz አማርኛ ርዕስ',
                'om' => 'Zz Afaan Oromoo',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $row = AboutContent::query()->find(AboutContent::SINGLETON_ID);

        $this->assertNotNull($row);

        // One column, one JSON document, and each language resolves through the
        // same text() the public pages read it with.
        $this->assertSame('Zz English Title', $row->text('heroTitle', 'en'));
        $this->assertSame('Zz አማርኛ ርዕስ', $row->text('heroTitle', 'am'));
        $this->assertSame('Zz Afaan Oromoo', $row->text('heroTitle', 'om'));

        /*
         * The half that used to be lost. Re-opening the screen shows all three
         * again, so an edit aimed at the English box cannot take the other two
         * with it — which is exactly what a single input over the column did.
         */
        Livewire::test(SingletonEditor::class, ['resource' => 'about-content'])
            ->assertSet('form.heroTitle.am', 'Zz አማርኛ ርዕስ')
            ->assertSet('form.heroTitle.om', 'Zz Afaan Oromoo')
            ->set('form.heroTitle.en', 'Zz English Title Edited')
            ->call('save')
            ->assertHasNoErrors();

        $row = $row->fresh();

        $this->assertSame('Zz English Title Edited', $row->text('heroTitle', 'en'));
        $this->assertSame('Zz አማርኛ ርዕስ', $row->text('heroTitle', 'am'));
        $this->assertSame('Zz Afaan Oromoo', $row->text('heroTitle', 'om'));
    }

    /**
     * A column still holding plain text — what the React input wrote — has to
     * land in the English box rather than be discarded. Otherwise the first open
     * and save of the screen would erase the only text the column had.
     */
    public function test_singleton_editor_lifts_a_plain_string_column_into_the_english_box(): void
    {
        $this->actingAs($this->staff());

        AboutContent::query()->updateOrCreate(
            ['id' => AboutContent::SINGLETON_ID],
            ['storyTitle' => 'Zz Plain Legacy Title'],
        );

        Livewire::test(SingletonEditor::class, ['resource' => 'about-content'])
            ->assertSet('form.storyTitle.en', 'Zz Plain Legacy Title')
            ->assertSet('form.storyTitle.am', '')
            ->call('save')
            ->assertHasNoErrors();

        $row = AboutContent::query()->find(AboutContent::SINGLETON_ID);

        $this->assertSame('Zz Plain Legacy Title', $row->text('storyTitle', 'en'));
    }

    /**
     * The required rule lands on the English box and not on the parent key: the
     * parent is an array, and `required` passes on a non-empty one even when all
     * three languages inside it are blank. Only English is required, so a page
     * can still be published before its translations exist.
     */
    public function test_singleton_editor_refuses_a_required_locale_field_left_blank_in_english(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(SingletonEditor::class, ['resource' => 'president-message'])
            ->set('form.name', 'Zz Chairman')
            ->set('form.position', ['en' => '', 'am' => 'Zz ማዕረግ', 'om' => 'Zz sadarkaa'])
            ->set('form.message', ['en' => '', 'am' => 'Zz መልእክት', 'om' => 'Zz ergaa'])
            ->call('save')
            ->assertHasErrors(['form.position.en', 'form.message.en']);

        Livewire::test(SingletonEditor::class, ['resource' => 'president-message'])
            ->set('form.name', 'Zz Chairman')
            ->set('form.position', ['en' => 'Zz Chairman', 'am' => 'Zz ማዕረግ', 'om' => 'Zz sadarkaa'])
            ->set('form.message', ['en' => 'Zz Message', 'am' => 'Zz መልእክት', 'om' => 'Zz ergaa'])
            ->call('save')
            ->assertHasNoErrors();

        $row = PresidentMessage::query()->find(PresidentMessage::SINGLETON_ID);

        $this->assertNotNull($row);
        $this->assertSame('Zz መልእክት', $row->text('message', 'am'));
        $this->assertSame('Zz ergaa', $row->text('message', 'om'));

        // `name` is a person's name, and stays one plain string in every row.
        $this->assertSame('Zz Chairman', $row->name);
    }

    public function test_singleton_editor_switches_tabs_and_grows_both_collection_shapes(): void
    {
        $this->actingAs($this->staff());

        $component = Livewire::test(SingletonEditor::class, ['resource' => 'landing-content'])
            // Six tabs; mount() opens the first and selectTab() has to move it.
            ->assertSet('tab', 'Community')
            ->call('selectTab', 'Impact')
            ->assertSet('tab', 'Impact')
            // An undeclared tab would render an empty screen, so it is ignored.
            ->call('selectTab', 'Zz Not A Tab')
            ->assertSet('tab', 'Impact')
            ->call('selectTab', 'Community');

        /*
         * Both collections are set to a known state first. The live row already
         * holds two seeded regions and a full set of six mosaic images, so an
         * assertion aimed at index 0 would read seeded data rather than the row
         * just added — and addRow() would refuse outright on the full one.
         *
         * A repeater stores objects and an imageList stores bare strings, and
         * HomeController reads the two through the same lt_json(), which is why
         * both shapes are grown here rather than one standing in for both.
         */
        $component
            ->set('form.communityImages', ['/uploads/zz-a.png', '/uploads/zz-b.png'])
            ->set('form.reachRegions', [])

            ->call('addRow', 'communityImages')
            ->call('setMediaUrl', 'communityImages', '/uploads/zz-mosaic.png', 2, null)
            ->assertSet('form.communityImages.2', '/uploads/zz-mosaic.png')

            ->call('addRow', 'reachRegions')
            ->assertSet('form.reachRegions.0.icon', '')
            ->set('form.reachRegions.0.region', 'Zz Region')
            ->set('form.reachRegions.0.count', '4 schools')
            ->call('save')
            ->assertHasNoErrors();

        $row = LandingContent::query()->find(LandingContent::SINGLETON_ID);

        $this->assertNotNull($row);
        $this->assertSame(['Zz Region'], array_column((array) json_decode((string) $row->reachRegions, true), 'region'));
        $this->assertStringContainsString('/uploads/zz-mosaic.png', (string) $row->communityImages);
    }

    /**
     * The cap is enforced in the component and not only by the hidden button:
     * addRow() is callable from the browser whatever the view rendered.
     */
    public function test_singleton_editor_will_not_grow_a_collection_past_its_max(): void
    {
        $this->actingAs($this->staff());

        $component = Livewire::test(SingletonEditor::class, ['resource' => 'landing-content'])
            ->set('form.communityImages', array_fill(0, 6, '/uploads/zz-full.png'))
            ->call('addRow', 'communityImages');

        $this->assertCount(6, (array) $component->get('form.communityImages'));
    }

    public function test_singleton_editor_places_an_upload_in_a_repeater_image_subfield(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(SingletonEditor::class, ['resource' => 'landing-content'])
            ->call('selectTab', 'Stories')
            // The seeded row holds a story already, so index 0 is not the new one.
            ->set('form.storiesItems', [])
            ->call('addRow', 'storiesItems')
            ->call('setMediaUrl', 'storiesItems', '/uploads/zz-story.png', 0, 'imageUrl')
            ->assertSet('form.storiesItems.0.imageUrl', '/uploads/zz-story.png')
            // `quote` is a textarea, not an image, so a URL aimed at it is dropped.
            ->call('setMediaUrl', 'storiesItems', '/uploads/zz-nope.png', 0, 'quote')
            ->assertSet('form.storiesItems.0.quote', '');
    }

    /* ── Media Library ────────────────────────────────────────────────────── */

    public function test_media_library_filters_renames_and_deletes(): void
    {
        $this->actingAs($this->staff());

        $image = Media::create([
            'url' => '/uploads/zz-library-image.png',
            'altText' => 'Zz original description',
            'fileType' => 'image',
            'fileSize' => 2048,
            'mimeType' => 'image/png',
        ]);

        $video = Media::create([
            'url' => '/uploads/zz-library-video.mp4',
            'fileType' => 'video',
            'fileSize' => 4096,
            'mimeType' => 'video/mp4',
        ]);

        Livewire::test(MediaLibrary::class, ['resource' => 'media'])
            ->assertSee('zz-library-image.png')
            ->assertSee('zz-library-video.mp4')

            ->call('setType', 'video')
            ->assertSet('filter', 'video')
            ->assertDontSee('zz-library-image.png')
            // An undeclared value would render an empty grid that looks like a
            // library with nothing in it, so setType() drops it.
            ->call('setType', 'banana')
            ->assertSet('filter', 'video')

            ->call('setType', '')
            ->set('search', 'zz-library-video')
            ->assertDontSee('zz-library-image.png')
            ->call('clearFilters')
            ->assertSet('search', '')

            ->call('openAlt', (string) $image->getKey())
            ->set('altText', 'Zz renamed description')
            ->call('saveAlt')
            ->assertHasNoErrors();

        $this->assertSame('Zz renamed description', $image->fresh()->altText);

        Livewire::test(MediaLibrary::class, ['resource' => 'media'])
            ->assertSee('Zz renamed description')
            ->call('delete', (string) $video->getKey());

        $this->assertNull(Media::query()->find($video->getKey()));

        $this->assertDatabaseHas('activitylog', ['entity' => 'Media', 'action' => 'update']);
        $this->assertDatabaseHas('activitylog', ['entity' => 'Media', 'action' => 'delete']);
    }

    /* ── Activity Logs ────────────────────────────────────────────────────── */

    public function test_activity_logs_filters_on_values_read_from_the_whole_table(): void
    {
        $this->actingAs($this->staff(User::ROLE_CONTENT_ADMIN));

        /*
         * Distinctive entity names and ids: the table already holds rows from
         * every module, so asserting on "Partner" would pass or fail on data
         * this test did not write.
         */
        $kept = \App\Models\ActivityLog::create([
            'action' => 'create', 'entity' => 'Zzalpha', 'entityId' => 'zz-alpha-id',
        ]);

        \App\Models\ActivityLog::create([
            'action' => 'delete', 'entity' => 'Zzbeta', 'entityId' => 'zz-beta-id',
        ]);

        Livewire::test(ActivityLogs::class, ['resource' => 'activity-logs'])
            ->assertSee('zz-alpha-id')
            ->assertSee('zz-beta-id')

            ->set('entity', 'Zzalpha')
            ->assertSee('zz-alpha-id')
            ->assertDontSee('zz-beta-id')

            /*
             * The React page built its dropdown from the rows it had already
             * filtered, so choosing an entity left it as the only option. An
             * arbitrary value arriving from the browser is reset to "all" rather
             * than left to render an empty table.
             */
            ->set('entity', 'Zz Not In The Table')
            ->assertSet('entity', '')
            ->assertSee('zz-beta-id')

            ->set('action', 'delete')
            ->assertSee('zz-beta-id')
            ->assertDontSee('zz-alpha-id');

        $this->assertNotNull($kept->fresh());
    }

    public function test_activity_logs_offers_no_way_to_write(): void
    {
        $this->actingAs($this->staff());

        $spec = new \App\Admin\Specs\ActivityLogSpec;

        // Empty, not null: null would inherit readRoles() and let the two
        // content roles write to the log through the component.
        $this->assertSame([], $spec->writeRoles());

        $before = \App\Models\ActivityLog::query()->count();

        Livewire::test(ActivityLogs::class, ['resource' => 'activity-logs'])
            ->set('entity', 'Zzgamma');

        $this->assertSame($before, \App\Models\ActivityLog::query()->count());
    }

    /* ── Staff Users ──────────────────────────────────────────────────────── */

    /**
     * `users` is derived from the class name rather than declared, because
     * Laravel's default for a model called User is exactly that and User
     * therefore carries no $table. The table was `user`, singular, inherited from
     * the Prisma schema it was ported from, and has since been renamed.
     *
     * Deriving is the convention, but it fails silently: renaming the class, or
     * re-adding an override, would repoint sign-in, UserManager's unique-email
     * rule and three foreign keys at a table that is not there, and nothing would
     * complain until an administrator was locked out. Hence pinned here.
     */
    public function test_the_user_model_sits_on_the_conventional_users_table(): void
    {
        $this->assertSame('users', (new User)->getTable());
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertFalse(
            Schema::hasTable('user'),
            'the old singular name should be gone, not left behind as a second copy',
        );

        /*
         * activitylog.userId, media.uploadedBy and blogpost.authorId are real
         * InnoDB foreign keys onto this table, so belongsTo is the direction
         * worth reading: it is the only one of the two that names the parent
         * table in its SQL. A hasMany binds the parent key as a value and would
         * quietly return nothing if the name went stale.
         */
        $author = $this->staff();

        $log = ActivityLog::query()->create([
            'userId' => $author->getKey(),
            'action' => 'zz-probe',
            'entity' => 'Zz',
        ]);

        $upload = Media::query()->create([
            'url' => '/uploads/zz-probe.png',
            'uploadedBy' => $author->getKey(),
        ]);

        $this->assertSame($author->email, $log->user->email);
        $this->assertSame($author->email, $upload->uploader->email);

        // A post needs a title, a slug and body copy to be worth writing, so
        // the third key is checked as the query it would run instead of a row.
        $this->assertStringContainsString('`users`', (new BlogPost)->author()->getQuery()->toSql());
    }

    public function test_user_manager_creates_a_staff_account_with_a_hashed_password(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(UserManager::class, ['resource' => 'users'])
            ->call('openCreate')
            ->set('form.name', 'Zz Editor Person')
            ->set('form.email', 'zz.editor.person@example.org')
            ->set('form.role', User::ROLE_EDITOR)
            ->set('form.password', 'secret123')
            ->call('save')
            ->assertHasNoErrors();

        $created = User::query()->where('email', 'zz.editor.person@example.org')->first();

        $this->assertNotNull($created);
        $this->assertSame(User::ROLE_EDITOR, $created->role);

        // Hashed, and stored in passwordHash — the column nothing else reads
        // from would have been "password", which does not exist.
        $this->assertNotSame('secret123', $created->passwordHash);
        $this->assertTrue($created->verifyPassword('secret123'));

        $this->assertDatabaseHas('activitylog', ['entity' => 'User', 'action' => 'create']);
    }

    public function test_user_manager_refuses_a_duplicate_email_and_a_short_password(): void
    {
        $this->actingAs($this->staff());

        Livewire::test(UserManager::class, ['resource' => 'users'])
            ->call('openCreate')
            ->set('form.name', 'Zz Duplicate')
            ->set('form.email', 'admin@nesim.org')
            ->set('form.role', User::ROLE_EDITOR)
            ->set('form.password', 'secret123')
            ->call('save')
            ->assertHasErrors('form.email');

        Livewire::test(UserManager::class, ['resource' => 'users'])
            ->call('openCreate')
            ->set('form.name', 'Zz Short Password')
            ->set('form.email', 'zz.short@example.org')
            ->set('form.role', User::ROLE_EDITOR)
            ->set('form.password', 'abc')
            ->call('save')
            ->assertHasErrors('form.password');
    }

    public function test_user_manager_leaves_the_password_alone_when_the_field_is_blank(): void
    {
        $admin = $this->staff();
        $this->actingAs($admin);

        $before = (string) $admin->fresh()->passwordHash;

        Livewire::test(UserManager::class, ['resource' => 'users'])
            ->call('openEdit', (string) $admin->getKey())
            ->assertSet('form.password', '')
            ->set('form.name', 'Zz Renamed Admin')
            ->call('save')
            ->assertHasNoErrors();

        $after = $admin->fresh();

        $this->assertSame('Zz Renamed Admin', $after->name);
        $this->assertSame($before, $after->passwordHash, 'a blank password box must not rehash');
    }

    public function test_user_manager_will_not_remove_the_admin_signed_in(): void
    {
        $admin = $this->staff();
        $this->actingAs($admin);

        Livewire::test(UserManager::class, ['resource' => 'users'])
            ->call('delete', (string) $admin->getKey())
            ->assertSet('error', 'You cannot delete your own account.');

        $this->assertNotNull(User::query()->find($admin->getKey()));
    }

    public function test_user_manager_will_not_demote_the_last_super_admin(): void
    {
        $admin = $this->staff();
        $this->actingAs($admin);

        /*
         * The seeded administrator holds SUPER_ADMIN too, so the guard would
         * always see a second one and pass. Demoting every other holder is what
         * makes the state this test is about reachable; DatabaseTransactions
         * rolls it back with the rest.
         */
        User::query()
            ->where('role', User::ROLE_SUPER_ADMIN)
            ->where('id', '!=', $admin->getKey())
            ->update(['role' => User::ROLE_CONTENT_ADMIN]);

        Livewire::test(UserManager::class, ['resource' => 'users'])
            ->call('openEdit', (string) $admin->getKey())
            ->set('form.role', User::ROLE_VIEWER)
            ->call('save')
            ->assertHasErrors('form.role')
            ->assertSet('error', 'This is the last super admin. Promote someone else first.');

        $this->assertSame(User::ROLE_SUPER_ADMIN, $admin->fresh()->role);

        // With a second super admin present the same change is allowed.
        $this->staff(User::ROLE_SUPER_ADMIN);

        Livewire::test(UserManager::class, ['resource' => 'users'])
            ->call('openEdit', (string) $admin->getKey())
            ->set('form.role', User::ROLE_CONTENT_ADMIN)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(User::ROLE_CONTENT_ADMIN, $admin->fresh()->role);
    }

    public function test_user_manager_removes_an_account_that_is_not_the_last_super_admin(): void
    {
        $admin = $this->staff();
        $this->actingAs($admin);

        $other = $this->staff(User::ROLE_EDITOR);

        Livewire::test(UserManager::class, ['resource' => 'users'])
            ->call('delete', (string) $other->getKey())
            ->assertSet('error', null);

        $this->assertNull(User::query()->find($other->getKey()));
        $this->assertDatabaseHas('activitylog', ['entity' => 'User', 'action' => 'delete']);
    }

    /* ── Helpers ──────────────────────────────────────────────────────────── */

    /**
     * A staff account created rather than fetched: the seeded admin is the only
     * SUPER_ADMIN in the database, and a test that borrowed it could not assert
     * anything about the last-super-admin guard without changing it first.
     */
    private function staff(string $role = User::ROLE_SUPER_ADMIN): User
    {
        $user = new User;

        $user->name = 'Zz Test '.Str::title(Str::snake($role, ' '));
        $user->email = 'zz.test.'.Str::lower(Str::random(10)).'@nesim.org';
        $user->role = $role;
        $user->setPassword('secret123');

        $user->save();

        return $user;
    }
}
