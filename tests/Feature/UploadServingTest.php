<?php

namespace Tests\Feature;

use App\Models\GlobalSettings;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the cPanel split-root upload fix: the app root and the served document
 * root are different directories, so an upload written under public_path() can
 * land outside the directory Apache actually serves. UploadController therefore
 * falls back to storage/app/public/uploads, and Site\UploadFileController serves
 * /uploads/{filename} from either location so the public URL is stable.
 */
class UploadServingTest extends TestCase
{
    private ?string $originalPublicPath = null;

    /** @var list<string> */
    private array $createdFiles = [];

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

        $this->actingAs(new User(['id' => 'upload-serving-test', 'role' => User::ROLE_SUPER_ADMIN]));
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        if ($this->originalPublicPath !== null) {
            app()->usePublicPath($this->originalPublicPath);
        }

        parent::tearDown();
    }

    private function track(string $path): string
    {
        $this->createdFiles[] = $path;

        return $path;
    }

    public function test_an_upload_uses_the_document_root_when_it_is_writable(): void
    {
        $response = $this->postJson('/admin/upload', [
            'file' => UploadedFile::fake()->image('zz-public-upload.png'),
        ])->assertOk();

        $url = (string) $response->json('url');
        $this->assertMatchesRegularExpression('#^/uploads/[A-Za-z0-9._-]+$#', $url);

        $path = $this->track(public_path(ltrim($url, '/')));
        $this->assertFileExists($path);

        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_an_upload_falls_back_to_the_public_disk_when_the_document_root_is_unavailable(): void
    {
        $this->originalPublicPath = public_path();

        // Simulate a document root whose uploads/ cannot be created: a regular
        // file occupies the name, so is_dir() fails and mkdir() cannot replace
        // it. UploadController must then fall back to storage/app/public/uploads.
        $isolatedPublicPath = sys_get_temp_dir().'/nesim-upload-'.uniqid();
        mkdir($isolatedPublicPath, 0777, true);
        $blocker = $isolatedPublicPath.'/uploads';
        file_put_contents($blocker, 'not a directory');
        app()->usePublicPath($isolatedPublicPath);

        $response = $this->postJson('/admin/upload', [
            'file' => UploadedFile::fake()->image('zz-storage-upload.png'),
        ])->assertOk();

        $url = (string) $response->json('url');
        $filename = basename($url);
        $this->assertMatchesRegularExpression('#^/uploads/[A-Za-z0-9._-]+$#', $url);

        // Nothing landed in the (blocked) document root...
        $this->assertFileDoesNotExist($isolatedPublicPath.'/uploads/'.$filename);

        // ...but it did land in the storage fallback, and is served from the URL.
        $stored = $this->track(storage_path('app/public/uploads/'.$filename));
        $this->assertFileExists($stored);

        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png');

        @unlink($blocker);
        @rmdir($isolatedPublicPath);
    }

    public function test_an_upload_stored_outside_the_document_root_is_served_from_its_public_url(): void
    {
        $filename = 'zz-storage-fallback-'.uniqid().'.png';
        $directory = storage_path('app/public/uploads');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        UploadedFile::fake()->image($filename)->storeAs('uploads', $filename, [
            'disk' => 'public',
        ]);
        $this->track($directory.'/'.$filename);

        $this->get('/uploads/'.$filename)->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_an_unknown_upload_is_not_found(): void
    {
        $this->get('/uploads/zz-missing-'.uniqid().'.png')->assertNotFound();
    }

    public function test_a_traversal_attempt_is_not_found(): void
    {
        $this->get('/uploads/..%2F..%2Findex.php')->assertNotFound();
    }
}
