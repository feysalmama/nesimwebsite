<?php

/**
 * Reads the three public homepages over HTTP and reports what a visitor would
 * actually see in the header, plus whether the framework's own language files
 * are present - the same class of bug, on the admin side.
 *
 * php storage/framework/verify-nav-fix.php
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\LocaleText;
use App\Support\SiteNav;
use Illuminate\Support\Facades\Http;

const BASE = 'http://127.0.0.1:8000';

/*
 * Every group name a translation key in this project can start with, so a label
 * that came back unresolved is recognisable in the rendered text.
 */
const PREFIXES = 'nav|footer|common|hero|impact|programs|projects|about|testimonials|volunteer|news|insights|faq|contact|register|donate|services|blog|gallery|resources|membership|leadership';

/** The text a browser would show, with scripts and styles removed first. */
function visibleText(string $html): string
{
    $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;

    return html_entity_decode(strip_tags($html), ENT_QUOTES);
}

$groups = count(array_filter(SiteNav::entries(), fn (array $e) => $e['children'] !== []));

foreach (LocaleText::LOCALES as $locale) {
    echo '=== /'.$locale.' ==='.PHP_EOL;

    $response = Http::get(BASE.'/'.$locale);

    if (! $response->successful()) {
        echo '  FAILED: HTTP '.$response->status().PHP_EOL.PHP_EOL;
        continue;
    }

    $html = $response->body();

    // -- raw keys anywhere in the visible text -------------------------------
    preg_match_all('/\b(?:'.PREFIXES.')\.[a-zA-Z][a-zA-Z.]*\b/', visibleText($html), $raw);
    $raw = array_values(array_unique($raw[0]));

    printf('  unresolved keys in the rendered text: %d%s', count($raw), PHP_EOL);

    foreach (array_slice($raw, 0, 8) as $key) {
        echo '    - '.$key.PHP_EOL;
    }

    // -- the slide-in panel ---------------------------------------------------
    $start = strpos($html, 'id="mobile-nav"');
    $end = $start === false ? false : strpos($html, '</header>', $start);

    if ($start === false || $end === false) {
        echo '  FAILED: no mobile panel in the response'.PHP_EOL.PHP_EOL;
        continue;
    }

    $panel = substr($html, $start, $end - $start);

    preg_match_all('/<a\s+href="([^"]*)"[^>]*>(.*?)<\/a>/s', $panel, $links);

    printf('  panel: %d anchors, %d of %d groups open%s', count($links[1]), substr_count($panel, 'data-accordion-toggle aria-expanded="true"'), $groups, PHP_EOL);

    foreach ($links[1] as $i => $href) {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($links[2][$i], ENT_QUOTES))));

        printf('    %-22s %s%s', $href, $text, PHP_EOL);
    }

    echo PHP_EOL;
}

echo '=== the framework language files ==='.PHP_EOL;

/*
 * lang/ holds three JSON files and no locale directories, so the messages
 * Laravel itself translates - validation, auth, pagination, passwords - have
 * nothing to resolve against. The admin forms call $this->validate() and render
 * the result through @error, so this is worth knowing about even though the
 * public pages have no forms yet.
 */
app()->setLocale(LocaleText::DEFAULT_LOCALE);

foreach ([
    'validation.required' => 'a required field left blank',
    'validation.email' => 'an email field that is not an address',
    'validation.unique' => 'UserManager\'s duplicate-email rule',
    'validation.min.string' => 'a password shorter than the minimum',
    'auth.failed' => 'the login page after a wrong password',
    'pagination.previous' => 'a paginated admin table',
    'passwords.sent' => 'a password reset, if one is ever added',
] as $key => $where) {
    $line = __($key);

    printf(
        "  %-10s %-24s %s%s",
        $line === $key ? 'RAW KEY' : 'resolved',
        $key,
        $where,
        PHP_EOL
    );
}

echo PHP_EOL;
