<?php

/*
 * Confirms the served page carries the mobile menu in the order the fix depends
 * on, and that it is serving the rebuilt bundle rather than a cached one.
 *
 * The PHPUnit test asserts the same thing against the response content; this is
 * the check against a real server, where a stale public/build or a cached
 * compiled view would still show the old markup.
 *
 * Usage: php storage/framework/live-navbar.php [base-url]
 *
 * Read-only: one GET request.
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');

$context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 60]]);
$html = (string) @file_get_contents($base.'/en', false, $context);

if ($html === '') {
    echo "no response from $base/en - is the server running?\n";

    exit(1);
}

$failures = 0;

$report = function (string $label, bool $ok) use (&$failures): void {
    if (! $ok) {
        $failures++;
    }

    printf("  %-6s %s\n", $ok ? 'ok' : 'FAIL', $label);
};

$pos = fn (string $needle) => strpos($html, $needle);

$headerEnd = $pos('</header>');
$backdrop = $pos('nav-mobile-backdrop');
$panel = $pos('id="mobile-nav"');
$main = $pos('<main');

printf("  positions: </header>=%s backdrop=%s panel=%s <main>=%s\n\n",
    var_export($headerEnd, true),
    var_export($backdrop, true),
    var_export($panel, true),
    var_export($main, true),
);

$report('the page has a header', $headerEnd !== false);
$report('the page has a backdrop', $backdrop !== false);
$report('the page has a slide-in panel', $panel !== false);

// The whole fix: both overlay elements after the header, so no backdrop-filter
// ancestor turns the header into their containing block.
$report('the backdrop comes after </header>', $backdrop !== false && $headerEnd !== false && $backdrop > $headerEnd);
$report('the panel comes after </header>', $panel !== false && $headerEnd !== false && $panel > $headerEnd);
$report('the panel comes before the page body', $panel !== false && $main !== false && $panel < $main);

$report(
    'the open flag is on <html>',
    (bool) preg_match('/<html[^>]+data-nav-open=/', $html),
);

// The header must not keep a copy of the flag: app.css no longer reads it there,
// and a stale attribute would only mislead the next person to look.
preg_match('/<header[^>]*>/s', $html, $headerTag);
$report('the header no longer carries data-nav-open', ! isset($headerTag[0]) || ! str_contains($headerTag[0], 'data-nav-open'));

/*
 * The bundle has to be the rebuilt one. Vite hashes the filename, so reading the
 * hash off the page and off the manifest catches a page still pointing at the
 * pre-fix CSS - which would render the new markup with the old selectors and
 * leave the menu stuck shut.
 */
preg_match('#assets/(app-[^"\']+\.css)#', $html, $cssMatch);
preg_match('#assets/(app-[^"\']+\.js)#', $html, $jsMatch);

$manifest = json_decode((string) @file_get_contents(__DIR__.'/../../public/build/manifest.json'), true) ?: [];

$report('the page links a stylesheet', isset($cssMatch[1]));
$report('the page links a script', isset($jsMatch[1]));

$wantCss = basename($manifest['resources/css/app.css']['file'] ?? '');
$wantJs = basename($manifest['resources/js/app.js']['file'] ?? '');

$report("the stylesheet is the built one ($wantCss)", ($cssMatch[1] ?? '') === $wantCss);
$report("the script is the built one ($wantJs)", ($jsMatch[1] ?? '') === $wantJs);

// And the built stylesheet really holds the relocated selectors.
$css = (string) @file_get_contents(__DIR__.'/../../public/build/assets/'.$wantCss);

$report('the built CSS keys the panel on html[data-nav-open]', str_contains($css, "html[data-nav-open=true] .nav-mobile-panel"));
$report('the built CSS no longer keys the panel on the header', ! str_contains($css, "header[data-nav-open=true] .nav-mobile-panel"));

$js = (string) @file_get_contents(__DIR__.'/../../public/build/assets/'.$wantJs);

$report('the built JS writes the flag to the document element', str_contains($js, 'documentElement'));

echo PHP_EOL;
echo $failures === 0 ? "ALL OK\n" : $failures." UNEXPECTED\n";

exit($failures === 0 ? 0 : 1);
