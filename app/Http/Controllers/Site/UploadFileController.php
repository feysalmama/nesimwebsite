<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves /uploads/{filename} when the web server cannot hand the file back
 * directly, which happens when the Laravel application and the document root
 * are separate directories and public/uploads is not writable.
 *
 * UploadController keeps the public URL stable either way, so existing database
 * values and the admin URL validation remain unchanged.
 */
class UploadFileController extends Controller
{
    private const ALLOWED_MIME = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'video/mp4', 'video/webm',
    ];

    public function __invoke(Request $request, string $filename): BinaryFileResponse
    {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            abort(404);
        }

        $path = $this->existingPath($filename);

        if ($path === null) {
            abort(404);
        }

        $mime = (string) mime_content_type($path);

        if (! in_array($mime, self::ALLOWED_MIME, true)) {
            abort(404);
        }

        return response()
            ->file($path)
            ->setMaxAge(31536000)
            ->setPublic();
    }

    private function existingPath(string $filename): ?string
    {
        foreach ([public_path('uploads'), storage_path('app/public/uploads')] as $directory) {
            $path = $directory.DIRECTORY_SEPARATOR.$filename;

            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }
}
