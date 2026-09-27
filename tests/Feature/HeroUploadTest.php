<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use App\Models\User;
use Dom\HTMLDocument;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HeroUploadTest extends TestCase
{
    public function test_only_the_first_hero_image_is_initially_visible(): void
    {
        $slides = collect([
            new HeroSlide(['imageUrl' => '/uploads/first.jpg', 'title' => 'First']),
            new HeroSlide(['imageUrl' => '/uploads/second.jpg', 'title' => 'Second']),
        ]);
        $html = (string) $this->view('components.hero-slider', ['slides' => $slides]);
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $elements = $document->querySelectorAll('[data-hero-slide]');

        $this->assertCount(2, $elements);
        $this->assertStringNotContainsString('opacity-0', $elements[0]->getAttribute('class'));
        $this->assertStringContainsString('opacity-0', $elements[1]->getAttribute('class'));
        $this->assertSame('/uploads/first.jpg', $elements[0]->querySelector('img')->getAttribute('src'));
        $this->assertStringContainsString('hidden', $document->querySelector('[data-hero-button]')->getAttribute('class'));
    }

    public function test_upload_reports_the_php_file_size_limit(): void
    {
        $user = new User(['id' => 'upload-test', 'role' => User::ROLE_SUPER_ADMIN]);
        $file = new UploadedFile('', 'hero.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);

        $this->actingAs($user)->postJson('/admin/upload', ['file' => $file])
            ->assertBadRequest()
            ->assertJsonPath('error', 'File too large. The server upload limit is '.ini_get('upload_max_filesize').'. Please choose a smaller file.');
    }

    public function test_upload_reports_the_php_request_size_limit(): void
    {
        $this->withServerVariables(['CONTENT_LENGTH' => PHP_INT_MAX])
            ->postJson('/admin/upload')
            ->assertStatus(413)
            ->assertJsonPath('error', 'Upload exceeds the server request limit of '.ini_get('post_max_size').'. Please choose a smaller file.');
    }
}
