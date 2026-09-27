<?php

namespace Tests\Feature;

use App\Livewire\Admin\ResourceManager;
use App\Models\Gallery;
use App\Models\GlobalSettings;
use App\Models\Program;
use App\Models\User;
use Dom\HTMLDocument;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminImageDisplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('media', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('url');
            $table->string('altText');
            $table->string('fileType');
            $table->integer('fileSize');
            $table->string('mimeType');
            $table->string('uploadedBy')->nullable();
            $table->timestamp('createdAt')->nullable();
        });
        Schema::create('globalsettings', function (Blueprint $table): void {
            $table->string('id')->primary();
        });
        GlobalSettings::flushResolved();
        $this->withoutVite();
    }

    public static function imageFields(): array
    {
        return [
            'gallery' => ['galleries', 'coverImage', 'gallery'],
            'project' => ['projects', 'imageUrl', 'project'],
            'program' => ['programs', 'imageUrl', 'program'],
            'news' => ['news', 'coverUrl', 'news'],
            'team' => ['team', 'photoUrl', 'team-member'],
        ];
    }

    #[DataProvider('imageFields')]
    public function test_upload_binds_to_the_admin_field_and_renders_in_its_card(string $resource, string $field, string $card): void
    {
        $this->actingAs(new User(['id' => 'test-editor', 'role' => User::ROLE_SUPER_ADMIN]));
        $response = $this->postJson('/admin/upload', ['file' => UploadedFile::fake()->image('photo.png')])->assertOk();
        $url = $response->json('url');
        $path = public_path($url);

        try {
            $this->assertFileExists($path);
            $this->assertSame('image/png', mime_content_type($path));
            $this->assertDatabaseHas('media', ['url' => $url, 'mimeType' => 'image/png']);

            $manager = new ResourceManager;
            $manager->resource = $resource;
            $manager->openCreate();
            $manager->setMediaUrl($field, $url);
            $this->assertSame($url, $manager->form[$field]);

            $html = (string) $this->view('components.cards.'.$card, [
                $field => $url, 'title' => 'Example', 'name' => 'Example',
                'summary' => 'Summary', 'excerpt' => 'Excerpt', 'role' => 'Teacher', 'href' => '/en',
            ]);
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            $this->assertSame($url, $document->querySelector('img')->getAttribute('src'));
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_programs_page_displays_the_uploaded_image(): void
    {
        $program = new Program(['title' => 'Example', 'summary' => 'Summary', 'imageUrl' => '/uploads/program.png']);

        $this->view('site.programs', ['programs' => collect([$program])])
            ->assertSee('src="/uploads/program.png"', false);
    }

    public function test_gallery_detail_displays_a_cover_without_album_photos(): void
    {
        $gallery = new Gallery(['title' => 'Example', 'coverImage' => '/uploads/gallery.png']);
        $gallery->setRelation('images', collect());

        $this->view('site.gallery.show', ['gallery' => $gallery])
            ->assertSee('src="/uploads/gallery.png"', false)
            ->assertDontSee('No images in this gallery yet.');
    }

    public function test_program_without_an_image_keeps_its_icon_fallback(): void
    {
        $this->view('components.cards.program', ['title' => 'Example', 'summary' => 'Summary', 'icon' => 'Book'])
            ->assertSee('Book')
            ->assertDontSee('<img', false);
    }
}
