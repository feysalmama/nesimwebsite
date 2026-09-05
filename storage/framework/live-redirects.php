<?php

/*
 * Reports redirect behaviour for the paths where it matters, over real HTTP
 * against a running server. Written in PHP rather than PowerShell because
 * Invoke-WebRequest hides the response object on a 3xx when redirects are
 * capped, and HttpClient construction proved unreliable in this shell - both
 * made /zz look like an error when it was behaving correctly.
 *
 * Read-only: GET requests only.
 *
 * Usage: php storage/framework/live-redirects.php [base-url]
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8123', '/');

/**
 * Fetch a URL without following redirects, so the first hop is visible.
 *
 * ignore_errors is what makes a 404 body readable instead of a warning; the
 * stream wrapper otherwise treats any non-2xx as a failure.
 */
function firstHop(string $url): array
{
    $context = stream_context_create(['http' => [
        'follow_location' => 0,
        'ignore_errors' => true,
        'max_redirects' => 0,
        'timeout' => 60,
    ]]);

    $body = (string) @file_get_contents($url, false, $context);

    $status = 0;
    $location = '';

    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) {
            $status = (int) $m[1];
        }

        if (stripos($header, 'Location:') === 0) {
            $location = trim(substr($header, 9));
        }
    }

    return ['status' => $status, 'location' => $location, 'body' => $body];
}

/**
 * Follow redirects to the end and report where the request finally landed.
 */
function followToEnd(string $url): array
{
    $context = stream_context_create(['http' => [
        'follow_location' => 1,
        'ignore_errors' => true,
        'max_redirects' => 10,
        'timeout' => 60,
    ]]);

    $body = (string) @file_get_contents($url, false, $context);

    $status = 0;
    $hops = 0;

    foreach ($http_response_header ?? [] as $header) {
        // One status line per redirect, so counting them counts the hops.
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) {
            $status = (int) $m[1];
            $hops++;
        }
    }

    return ['status' => $status, 'hops' => max(0, $hops - 1), 'body' => $body];
}

$failures = 0;

$report = function (string $label, bool $ok) use (&$failures): void {
    if (! $ok) {
        $failures++;
    }

    printf("  %-6s %s\n", $ok ? 'ok' : 'FAIL', $label);
};

echo 'first hop, redirects disabled', PHP_EOL;

/*
 * /zz is not a locale, so the fallback prefixes it and tries again; /en/zz is
 * already prefixed and serves nothing, so the fallback must 404 it outright.
 * That second step is the guard in routes/web.php - without it the fallback
 * would prefix /en/zz into /en/en/zz and loop until the browser gave up.
 */
foreach ([
    '/zz' => [302, '/en/zz'],
    '/en/zz' => [404, null],
    '/am/nothing-here' => [404, null],
    '/en/nothing' => [404, null],
    '/en/blog/no-such-post' => [404, null],
    '/donate' => [302, '/en/donate'],
    '/' => [302, '/en'],
] as $pathArg => [$wantStatus, $wantLocation]) {
    $hop = firstHop($base.$pathArg);

    // Laravel emits an absolute Location, so compare only its path - matching
    // the whole header would fail on every redirect purely over the scheme and
    // host this script was pointed at.

    $path = $hop['location'] === '' ? '' : (string) (parse_url($hop['location'], PHP_URL_PATH) ?? '');

    $ok = $hop['status'] === $wantStatus
        && ($wantLocation === null || $path === $wantLocation);

    $report(
        sprintf('%-26s %d -> %s (want %d%s)',
            $pathArg,
            $hop['status'],
            $hop['location'] === '' ? '-' : $path,
            $wantStatus,
            $wantLocation === null ? '' : ' -> '.$wantLocation
        ),
        $ok
    );
}

echo PHP_EOL, 'redirects followed to the end', PHP_EOL;

// The reported symptom was the placeholder copy, so its absence is the
// assertion. A bare 200 would also pass on any unrelated page.
foreach ([
    '/donate', '/blog', '/membership', '/volunteer', '/contact', '/faq',
    '/leadership', '/impact', '/testimonials', '/programs', '/services',
    '/projects', '/news', '/gallery', '/resources',
] as $path) {
    $end = followToEnd($base.$path);

    $ok = $end['status'] === 200 && ! str_contains($end['body'], 'has not been ported yet');

    $report(sprintf('%-16s %d in %d hop(s), placeholder=%s',
        $path,
        $end['status'],
        $end['hops'],
        str_contains($end['body'], 'has not been ported yet') ? 'yes' : 'no'
    ), $ok);
}

echo PHP_EOL, 'unknown locale settles instead of looping', PHP_EOL;

$end = followToEnd($base.'/zz');
$report(sprintf('/zz ends at %d after %d hop(s)', $end['status'], $end['hops']), $end['status'] === 404 && $end['hops'] <= 2);

$end = followToEnd($base.'/en/zz');
$report(sprintf('/en/zz ends at %d after %d hop(s)', $end['status'], $end['hops']), $end['status'] === 404 && $end['hops'] === 0);

echo PHP_EOL;
echo $failures === 0 ? "ALL OK\n" : $failures." UNEXPECTED\n";

exit($failures === 0 ? 0 : 1);
