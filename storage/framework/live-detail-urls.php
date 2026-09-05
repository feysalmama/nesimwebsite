<?php

/*
 * Prints one real detail URL per section per locale, for live-check.ps1 to hit
 * over HTTP. The rows come straight from the database so the check exercises
 * slugs and ids that actually exist rather than hardcoded guesses, and it stays
 * current as editors publish and unpublish. Read-only.
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\NewsPost;
use App\Models\Project;
use App\Models\Service;
use App\Support\LocaleText;

// Each entry is a route and the query that finds a row it can serve. The
// published filter matters: an unpublished row 404s on purpose, so picking one
// here would report a pass as a failure.
$sections = [
    'blog/{slug}' => fn () => BlogPost::where('published', true)->orderBy('createdAt', 'desc')->first(),
    'services/{slug}' => fn () => Service::where('published', true)->orderBy('order', 'asc')->first(),
    'projects/{id}' => fn () => Project::where('published', true)->orderBy('createdAt', 'desc')->first(),
    'news/{id}' => fn () => NewsPost::where('published', true)->orderBy('publishedAt', 'desc')->first(),
    'gallery/{id}' => fn () => Gallery::where('published', true)->orderBy('createdAt', 'desc')->first(),
];

foreach ($sections as $pattern => $finder) {
    $row = $finder();

    if ($row === null) {
        fwrite(STDERR, 'no published row for '.$pattern.PHP_EOL);

        continue;
    }

    // {slug} routes key off the slug, {id} routes off the cuid primary key.
    $key = str_contains($pattern, '{slug}') ? $row->slug : $row->id;
    $path = '/'.str_replace(['{slug}', '{id}'], $key, $pattern);

    foreach (LocaleText::LOCALES as $locale) {
        echo '/'.$locale.$path, PHP_EOL;
    }
}
