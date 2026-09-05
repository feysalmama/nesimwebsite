<?php

/*
 * GETs the public pages the Laravel app actually serves and reports what each
 * one rendered.
 *
 * The companion to smoke-admin.php, written for the same reason: the feature
 * suite asserts these pages through Laravel's own request pipeline, which is not
 * quite the same claim as a browser's. Two things only show up over real HTTP —
 * the compiled Blade cache being stale, and Vite's manifest not matching what
 * the layout asks for.
 *
 * The check that matters most is the last one. Content columns hold
 * {"en":…,"am":…,"om":…}, and a page that reads one raw instead of through
 * BaseModel::text() prints that document into the markup. It is invisible in a
 * status code and obvious to anyone who visits the site.
 *
 * Usage:  php storage/framework/smoke-public.php [base-url]
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');

function get(string $url): array
{
    $handle = curl_init($url);

    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        // Off, so the redirect off `/` is reported as the redirect it is rather
        // than as the page it lands on.
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 120,
    ]);

    $body = (string) curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $location = (string) curl_getinfo($handle, CURLINFO_REDIRECT_URL);

    // No curl_close(): it has done nothing since PHP 8.0 and is deprecated in
    // 8.5, so calling it filled the report with one warning per request.
    unset($handle);

    return [$status, $body, $location];
}

$failed = [];

/* ── The locale prefix ───────────────────────────────────────────────────── */

[$status, , $location] = get($base.'/');

$ok = $status === 302 && str_ends_with(rtrim($location, '/'), '/en');

printf("%-16s %-6d -> %-24s %s\n", '/', $status, $location === '' ? '(nowhere)' : $location, $ok ? '' : '<-- BROKEN');

if (! $ok) {
    $failed[] = '/';
}

/* ── Every ported page, in every language ────────────────────────────────── */

$pages = ['', 'about'];
$locales = ['en', 'am', 'om'];

echo str_repeat('-', 78)."\n";
printf("%-16s %-6s %-9s %-8s %s\n", 'PATH', 'HTTP', 'BYTES', 'LANG', 'NOTES');
echo str_repeat('-', 78)."\n";

$headings = [];

foreach ($pages as $page) {
    foreach ($locales as $locale) {
        $path = '/'.$locale.($page === '' ? '' : '/'.$page);

        [$status, $html] = get($base.$path);

        $notes = [];

        $broken = $status !== 200
            || str_contains($html, 'Whoops, looks like something went wrong')
            || str_contains($html, 'syntax error, unexpected')
            || str_contains($html, 'Undefined variable');

        // <html lang="…"> is what SetLocale wrote, and what a search engine and
        // a screen reader both go by.
        $lang = preg_match('/<html[^>]+\blang="([a-z]{2})"/', $html, $m) === 1 ? $m[1] : '(none)';

        if ($lang !== $locale) {
            $broken = true;
            $notes[] = 'lang is '.$lang;
        }

        // A locale document reaching the screen unresolved.
        if (str_contains($html, '{"en":') || str_contains($html, '{&quot;en&quot;:')) {
            $broken = true;
            $notes[] = 'encoded locale JSON on screen';
        }

        /*
         * The hero heading, kept per page so the three languages can be compared
         * against each other below. A page that resolved every prefix to English
         * would pass every check above it.
         */
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $html, $m) === 1) {
            $headings[$page][$locale] = preg_replace('/\s+/u', ' ', trim(strip_tags($m[1])));
        }

        if ($broken) {
            $failed[] = $path;
        }

        printf(
            "%-16s %-6d %-9s %-8s %s\n",
            $path,
            $status,
            number_format(strlen($html)).' B',
            $lang,
            $notes === [] ? ($broken ? '<-- BROKEN' : '') : '<-- '.implode('; ', $notes),
        );
    }
}

/* ── The three languages are not the same page ───────────────────────────── */

echo str_repeat('-', 78)."\n";

foreach ($headings as $page => $perLocale) {
    $distinct = count(array_unique($perLocale));

    $label = $page === '' ? 'homepage' : $page;

    printf(
        "%-16s %d distinct h1 across %d locales%s\n",
        $label,
        $distinct,
        count($perLocale),
        $distinct > 1 ? '' : '   <-- every prefix served the same text',
    );
}

echo str_repeat('-', 78)."\n";

$total = 1 + count($pages) * count($locales);

echo $total.' requests, '.count($failed)." broken\n";

if ($failed !== []) {
    echo 'broken: '.implode(', ', $failed)."\n";

    exit(1);
}
