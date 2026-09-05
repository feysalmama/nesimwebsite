<?php

/**
 * Rewrites lang/{locale}.json from nested objects into the flat, dotted keys
 * Laravel actually looks up.
 *
 * Why this is needed, in the framework's own words. Translator::get() resolves a
 * JSON line with a plain array index:
 *
 *     $this->load('*', '*', $locale);
 *     $line = $this->loaded['*']['*'][$locale][$key] ?? null;
 *
 * and the comment above those two lines says JSON translations "are only one
 * level deep so we do not need to do any fancy searching through it". So
 * __("nav.home") asks the decoded document for an entry literally keyed
 * "nav.home". These files were carried over from next-intl's messages/*.json,
 * where the nesting is the lookup mechanism, so their top-level keys are "nav",
 * "hero", "impact" ... and the literal "nav.home" is not among them.
 *
 * The translator then falls back to the PHP-file path - group "nav", item
 * "home", i.e. lang/en/nav.php - which does not exist either. Two dead ends, one
 * symptom: the header rendered "nav.home" on every link.
 *
 * Flattening is done to the files rather than around them, because the flat form
 * is the one Laravel documents and it needs no custom loader, no service
 * provider and nothing for the next person to know about.
 *
 * Nothing is re-authored here: the strings are moved, not rewritten, and the
 * script refuses to touch a file unless the flattened document decodes back to
 * exactly the same key/value pairs the nested one held. The originals are in git.
 *
 * php storage/framework/flatten-lang.php
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\LocaleText;

const LANG_DIR = __DIR__.'/../../lang';

/** Collapse a nested translation document into dotted keys, preserving order. */
function flatten(array $lines, string $prefix = ''): array
{
    $out = [];

    foreach ($lines as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

        if (is_array($value)) {
            foreach (flatten($value, $path) as $nested => $text) {
                $out[$nested] = $text;
            }

            continue;
        }

        $out[$path] = is_scalar($value) ? (string) $value : '';
    }

    return $out;
}

/**
 * Count the scalar leaves of a nested document the long way round. An empty
 * array anywhere in the tree would contribute no dotted key and so vanish
 * silently from flatten(), which this total is here to catch.
 */
function countLeaves(array $lines): int
{
    $total = 0;

    foreach ($lines as $value) {
        $total += is_array($value) ? countLeaves($value) : 1;
    }

    return $total;
}

/** Read the same dotted path back out of the original nested document. */
function nestedLookup(array $lines, string $path): mixed
{
    $cursor = $lines;

    foreach (explode('.', $path) as $segment) {
        if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
            return null;
        }

        $cursor = $cursor[$segment];
    }

    return $cursor;
}

$written = 0;

foreach (LocaleText::LOCALES as $locale) {
    $path = LANG_DIR.'/'.$locale.'.json';

    echo '=== '.$locale.'.json ==='.PHP_EOL;

    $raw = file_get_contents($path);
    $nested = json_decode($raw, true);

    if (! is_array($nested)) {
        echo '  ABORTED: not valid JSON - '.json_last_error_msg().PHP_EOL;
        exit(1);
    }

    /*
     * Already flat when no top-level value is an array. Re-running this script
     * must be a no-op rather than a second pass over keys that already contain
     * dots.
     */
    $isFlat = true;

    foreach ($nested as $value) {
        if (is_array($value)) {
            $isFlat = false;
            break;
        }
    }

    if ($isFlat) {
        printf('  already flat, %d keys - nothing to do%s', count($nested), PHP_EOL.PHP_EOL);
        continue;
    }

    $flat = flatten($nested);

    printf('  %d top-level groups -> %d dotted keys%s', count($nested), count($flat), PHP_EOL);

    $leaves = countLeaves($nested);

    if ($leaves !== count($flat)) {
        printf(
            '  ABORTED: the document holds %d strings but flattening produced %d keys.%s',
            $leaves,
            count($flat),
            PHP_EOL
        );
        exit(1);
    }

    // Every leaf of the original must survive at its dotted path, unchanged.
    $mismatched = [];

    foreach ($flat as $key => $value) {
        $original = nestedLookup($nested, $key);

        if (! is_string($original) || $original !== $value) {
            $mismatched[] = $key;
        }
    }

    if ($mismatched !== []) {
        echo '  ABORTED: '.count($mismatched)." key(s) did not survive flattening:".PHP_EOL;

        foreach (array_slice($mismatched, 0, 10) as $key) {
            echo '    - '.$key.PHP_EOL;
        }

        exit(1);
    }

    /*
     * JSON_UNESCAPED_UNICODE because two of the three locales are Amharic and
     * Afaan Oromoo, written in Ge'ez and Latin script; \uXXXX escapes would make
     * the files unreadable to the translators who maintain them. The source
     * files are already raw UTF-8, so this keeps the diff to the key shape.
     */
    $encoded = json_encode(
        $flat,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if (! is_string($encoded)) {
        echo '  ABORTED: could not encode - '.json_last_error_msg().PHP_EOL;
        exit(1);
    }

    // Encode, then decode what was encoded, and only write once they agree.
    $roundTrip = json_decode($encoded, true);

    if ($roundTrip !== $flat) {
        echo '  ABORTED: the encoded document does not decode back to the same keys.'.PHP_EOL;
        exit(1);
    }

    file_put_contents($path, $encoded.PHP_EOL);

    $onDisk = json_decode((string) file_get_contents($path), true);

    if ($onDisk !== $flat) {
        echo '  FAILED: what is on disk is not what was verified.'.PHP_EOL;
        exit(1);
    }

    $written++;

    printf('  written and read back identical, %d keys%s', count($onDisk), PHP_EOL);
    printf('  sample: nav.home = %s%s', $onDisk['nav.home'] ?? '(absent)', PHP_EOL.PHP_EOL);
}

echo '=== through the translator ==='.PHP_EOL;

foreach (LocaleText::LOCALES as $locale) {
    app()->setLocale($locale);

    printf(
        "  %-3s nav.home=%-14s nav.about=%-16s footer.explore=%s%s",
        $locale,
        __('nav.home'),
        __('nav.about'),
        __('footer.explore'),
        PHP_EOL
    );
}

app()->setLocale(LocaleText::DEFAULT_LOCALE);

echo PHP_EOL;

if ($written === count(LocaleText::LOCALES)) {
    printf('OK: all %d language files flattened.%s', $written, PHP_EOL);
} else {
    printf('OK: %d file(s) rewritten, the rest were already flat.%s', $written, PHP_EOL);
}
