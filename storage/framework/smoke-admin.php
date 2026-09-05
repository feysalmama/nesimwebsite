<?php

/*
 * Signs in to the CMS over HTTP and GETs every admin route, reporting the status
 * and the heading each one renders.
 *
 * Written because "the route is registered" and "the screen renders" are not the
 * same claim: AdminNav::SPECS drives the route constraint, so a typo in a spec's
 * component() or a Blade error in one of the four panels shows up as a 500 on
 * exactly one URL and nowhere else.
 *
 * Usage:  php storage/framework/smoke-admin.php [base-url]
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');
$jar = sys_get_temp_dir().'/nesim-admin-cookies-'.getmypid().'.txt';

@unlink($jar);

function request(string $url, ?array $post = null, string $jar = ''): array
{
    $handle = curl_init($url);

    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_TIMEOUT => 120,
    ]);

    if ($post !== null) {
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    $body = (string) curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $effective = (string) curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);

    // No curl_close(): it has done nothing since PHP 8.0 and is deprecated in
    // 8.5, so calling it filled the report with one warning per request.
    unset($handle);

    return [$status, $body, $effective];
}

function token(string $html): string
{
    if (preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m) === 1) {
        return $m[1];
    }

    if (preg_match('/content="([^"]+)"\s+name="csrf-token"/', $html, $m) === 1) {
        return $m[1];
    }

    return '';
}

/*
 * The rendered markup with Livewire's own payload attributes taken out.
 *
 * wire:snapshot carries every public property as entity-encoded JSON, and a
 * locale field's property IS {"en":…,"am":…,"om":…} — so searching the raw
 * response for an encoded document finds the component's state on every page
 * that has one and says nothing about what the editor was shown. What is left
 * after stripping it is the visible screen.
 */
function visible(string $html): string
{
    return (string) preg_replace('/\s+wire:(snapshot|effects)="[^"]*"/', '', $html);
}

/*
 * A locale column reaching the screen still encoded. cell() prints (string)
 * $value, so a column listed in columns() without a `render` puts the whole
 * document in the table cell — which is what the category pickers did before
 * they decoded the English name.
 */
function leaksLocaleJson(string $html): bool
{
    $shown = visible($html);

    return str_contains($shown, '{&quot;en&quot;:') || str_contains($shown, '{"en":');
}

/* ── Sign in ─────────────────────────────────────────────────────────────── */

[$status, $html] = request($base.'/admin/login', null, $jar);

$csrf = token($html);

if ($status !== 200 || $csrf === '') {
    echo "LOGIN PAGE FAILED (HTTP {$status}, token ".($csrf === '' ? 'not found' : 'found').")\n";

    exit(1);
}

[$status, $html, $effective] = request($base.'/admin/login', [
    '_token' => $csrf,
    'email' => 'admin@nesim.org',
    'password' => 'Nesim@2026',
], $jar);

if (! str_contains($effective, '/admin') || str_contains($effective, 'login')) {
    echo "SIGN-IN FAILED (HTTP {$status}, landed on {$effective})\n";
    echo strip_tags(substr($html, 0, 600))."\n";

    exit(1);
}

echo "signed in as admin@nesim.org (HTTP {$status})\n\n";

/* ── Every route the sidebar offers ──────────────────────────────────────── */

require __DIR__.'/../../vendor/autoload.php';

$slugs = array_map(
    static fn (array $item) => $item['slug'],
    array_merge(...array_map(
        static fn (array $group) => $group['items'],
        \App\Support\AdminNav::groupsForRole('SUPER_ADMIN'),
    )),
);

$failed = [];

/*
 * The two singleton screens whose columns hold three languages, and the input
 * ids their locale branch is expected to emit. A 200 with a heading would pass
 * the check below while still rendering one box over the encoded document, so
 * the boxes themselves are counted.
 */
$localeScreens = [
    'about-content' => ['heroTitle', 'heroSubtitle', 'storyTitle', 'storyBody', 'missionText', 'visionText', 'valuesText'],
    'president-message' => ['position', 'message'],
];

printf("%-24s %-6s %-9s %s\n", 'ROUTE', 'HTTP', 'BYTES', 'HEADING');
echo str_repeat('-', 78)."\n";

foreach ($slugs as $slug) {
    $url = $base.($slug === 'dashboard' ? '/admin' : '/admin/'.$slug);

    [$status, $html] = request($url, null, $jar);

    $heading = preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $html, $m) === 1
        ? preg_replace('/\s+/u', ' ', trim(strip_tags($m[1])))
        : '(none)';

    /*
     * A 200 is not proof of a rendered screen. Livewire renders its component
     * server-side into the page, so a Blade error inside one is a 500, but an
     * error page can also come back with the heading of the layout around it —
     * hence the markers.
     */
    $broken = $status !== 200
        || str_contains($html, 'Whoops, looks like something went wrong')
        || str_contains($html, 'syntax error, unexpected')
        || str_contains($html, 'Undefined variable');

    $notes = [];

    if (! $broken && leaksLocaleJson($html)) {
        $broken = true;
        $notes[] = 'encoded locale JSON on screen';
    }

    if (! $broken && isset($localeScreens[$slug])) {
        $missing = [];

        foreach ($localeScreens[$slug] as $field) {
            foreach (['en', 'am', 'om'] as $locale) {
                if (! str_contains($html, 'id="se-'.$field.'-'.$locale.'"')) {
                    $missing[] = $field.'.'.$locale;
                }
            }
        }

        if ($missing !== []) {
            $broken = true;
            $notes[] = 'no locale box for '.implode(', ', $missing);
        } else {
            $notes[] = (count($localeScreens[$slug]) * 3).' locale boxes';
        }
    }

    if ($broken) {
        $failed[] = $slug;
    }

    printf(
        "%-24s %-6d %-9s %s%s\n",
        '/admin'.($slug === 'dashboard' ? '' : '/'.$slug),
        $status,
        number_format(strlen($html)).' B',
        mb_substr($heading, 0, 32),
        $notes === [] ? ($broken ? '   <-- BROKEN' : '') : '   '.($broken ? '<-- ' : '').implode('; ', $notes),
    );
}

echo str_repeat('-', 78)."\n";
echo count($slugs)." routes checked, ".count($failed)." broken\n";

if ($failed !== []) {
    echo 'broken: '.implode(', ', $failed)."\n";

    exit(1);
}

@unlink($jar);
