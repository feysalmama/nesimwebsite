<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Port of app/api/upload/route.ts. Same contract the admin UI already speaks:
 * POST a multipart "file" field, get back {"url": "/uploads/<name>"}.
 *
 * This is also where the port structurally removes the bug that made uploads
 * appear broken in the Next.js app. There, files were written into public/ at
 * runtime but Next.js serves public/ from a manifest built at compile time, so a
 * fresh upload 404'd until the next build. Here the file lands in the document
 * root and PHP hands it back on the very next request — no build step exists to
 * go stale.
 *
 * Files are written directly to public/uploads rather than through the "public"
 * disk (which resolves to storage/app/public), because that is where every
 * existing Media row's url already points.
 *
 * That directory used to be a junction onto the Next.js app's public/uploads so
 * both stacks shared one folder. It is a real directory now, holding its own
 * verified copy of the same 71 files - see storage/framework/adopt-uploads.ps1 -
 * so this app no longer reaches outside itself for its images.
 */
class UploadController extends Controller
{
    /** Mirrors the `allowed` list in the old route. */
    private const ALLOWED_MIME = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'video/mp4', 'video/webm',
    ];

    private const MAX_BYTES = 25 * 1024 * 1024;

    public function __invoke(Request $request): JsonResponse
    {
        $file = $request->file('file');

        if (! $file instanceof UploadedFile) {
            return response()->json(['error' => 'No file provided'], 400);
        }

        if (! $file->isValid()) {
            // upload_max_filesize / post_max_size rejections surface here as a
            // PHP upload error rather than an exception, so say which it was.
            return response()->json(['error' => $this->uploadError($file)], 400);
        }

        $mime = $file->getMimeType() ?: '';

        if (! in_array($mime, self::ALLOWED_MIME, true)) {
            return response()->json(['error' => 'Unsupported file type'], 400);
        }

        if ((int) $file->getSize() > self::MAX_BYTES) {
            return response()->json(['error' => 'File too large (25MB max)'], 400);
        }

        $directory = public_path('uploads');

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return response()->json(['error' => 'Upload directory is not writable'], 500);
        }

        // Read before move(): once the temp file is gone getSize() returns false
        // and the Media row would be recorded with a null byte count.
        $size = (int) $file->getSize();

        $filename = $this->filename($file);

        /*
         * The name is generated here, never taken from the client, so the only
         * way it could escape the directory is a bug above. basename() is the
         * check that catches one: a name that survives the round trip holds no
         * path separator and no leading dot, so move() can only place it inside
         * $directory.
         */
        if (basename($filename) !== $filename) {
            return response()->json(['error' => 'Invalid filename'], 400);
        }

        $file->move($directory, $filename);

        $url = '/uploads/'.$filename;

        try {
            Media::create([
                'url' => $url,
                'altText' => $file->getClientOriginalName(),
                'fileType' => str_starts_with($mime, 'video/') ? 'video' : 'image',
                'fileSize' => $size,
                'mimeType' => $mime,
                'uploadedBy' => $request->user()?->id,
            ]);
        } catch (\Throwable $e) {
            // Same trade-off the Next.js route made: the file is on disk and
            // usable, so the upload is not failed over a missing Media Library
            // row — but the row must not disappear silently either.
            Log::error('[upload] file saved but media row failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['url' => $url]);
    }

    /**
     * "<millis>-<6 base36 chars>.<ext>", matching the old filename format so
     * existing rows and new ones stay visually consistent in the media library.
     */
    private function filename(UploadedFile $file): string
    {
        // path.extname() in the old code returned "" for an extensionless name,
        // which an earlier split(".").pop() had turned into the whole filename.
        // Only alphanumerics survive, so a crafted name cannot inject path
        // separators or a dot that would change how the file is served.
        $ext = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';

        $millis = (int) round(microtime(true) * 1000);

        return $millis.'-'.Str::lower(Str::random(6)).'.'.$ext;
    }

    /** Translate a PHP upload error code into the message the UI can show. */
    private function uploadError(UploadedFile $file): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File too large (server limit)',
            UPLOAD_ERR_PARTIAL => 'Upload was interrupted, please retry',
            UPLOAD_ERR_NO_FILE => 'No file provided',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'Server could not write the upload',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension',
            default => 'Upload failed',
        };
    }
}
