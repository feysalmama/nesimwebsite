<?php

/*
 * Which text columns actually hold a three-language document, and what type does
 * each spec declare for them?
 *
 * The React admin declared `type: "text"` for several locale-JSON columns —
 * hero-slides' title, subtitle and buttonText among them — so saving a row
 * there replaced {"en":…,"am":…,"om":…} with plain English and silently
 * dropped two of the site's three languages. This compares the database against
 * the specs so every instance is found at once instead of one at a time.
 */
require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\AdminNav;
use Illuminate\Support\Facades\DB;

$specs = AdminNav::specs();

echo count($specs), " specs registered\n\n";

printf("%-26s %-22s %-16s %-16s %s\n", 'MODULE', 'FIELD', 'SPEC TYPE', 'DB TYPE', 'STORED AS');
echo str_repeat('-', 100), PHP_EOL;

$mismatches = [];
$notes = [];

foreach ($specs as $slug => $class) {
    $spec = new $class;

    /*
     * ResourceSpec and SubmissionSpec declare a flat fields() list; SingletonSpec
     * declares sections(), each with its own fields. Both shapes end up writing
     * to real columns, so both are audited. A repeater's subfields are skipped:
     * they live inside one JSON column and have no column of their own to
     * compare against.
     */
    $fields = [];

    if (method_exists($spec, 'fields')) {
        $fields = $spec->fields();
    } elseif (method_exists($spec, 'sections')) {
        foreach ($spec->sections() as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $fields[] = $field;
            }
        }
    }

    if ($fields === []) {
        continue;
    }

    $model = $spec->model();
    $table = (new $model)->getTable();

    $dbTypes = [];

    foreach (DB::select(
        'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH AS len
         FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
        [$table]
    ) as $column) {
        $dbTypes[$column->COLUMN_NAME] = $column->DATA_TYPE
            . ($column->len !== null ? '(' . $column->len . ')' : '');
    }

    foreach ($fields as $field) {
        $name = $field['name'];

        if (! isset($dbTypes[$name])) {
            continue;
        }

        // What the rows really contain.
        $sample = DB::table($table)->whereNotNull($name)->limit(40)->pluck($name);
        $locale = 0;
        $plain = 0;

        foreach ($sample as $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            $decoded = json_decode($value, true);

            if (is_array($decoded) && array_key_exists('en', $decoded)) {
                $locale++;
            } else {
                $plain++;
            }
        }

        $storedAs = $locale > 0 && $plain === 0 ? 'locale-json'
            : ($locale > 0 ? 'MIXED' : ($plain > 0 ? 'plain' : 'empty'));

        $declaredLocale = in_array($field['type'], ['localeText', 'localeTextarea'], true);

        /*
         * MIXED means some rows hold a locale document and some hold plain text.
         * Under a plain `text` declaration that is the bug this script exists to
         * find. Under a locale declaration it is not one: LocaleText::parse()
         * lifts a bare string into `en` and get() returns it unchanged, so a
         * legacy row survives the round trip and the next save normalises it.
         * Worth printing, not worth counting.
         */
        $wrong = ! $declaredLocale && in_array($storedAs, ['locale-json', 'MIXED'], true);
        $note = $declaredLocale && $storedAs === 'MIXED';

        if ($wrong || $note) {
            if ($wrong) {
                $mismatches[] = $slug . '.' . $name;
            } else {
                $notes[] = $slug . '.' . $name;
            }

            printf(
                "%-26s %-22s %-16s %-16s %s  %s\n",
                $slug, $name, $field['type'], $dbTypes[$name], $storedAs,
                $wrong ? '<<<' : '(legacy rows, handled)'
            );
        }
    }
}

echo PHP_EOL, 'mismatches: ', count($mismatches), PHP_EOL;

foreach ($mismatches as $item) {
    echo '  ', $item, PHP_EOL;
}

if ($notes !== []) {
    echo PHP_EOL, 'declared locale but holding some plain-text rows: ', count($notes), PHP_EOL;

    foreach ($notes as $item) {
        echo '  ', $item, PHP_EOL;
    }
}
