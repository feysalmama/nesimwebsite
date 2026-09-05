<?php

/*
 * Copies the full-length originals back into the values MySQL truncated.
 *
 * The scratch database `nesim_restore` holds the same seed as `nesim`, but built
 * on TEXT columns, so nothing in it was cut. Its cuids are different, so rows
 * cannot be joined on id — matching is done on content instead, in three passes
 * of decreasing directness:
 *
 *   A  the live value is a plain prefix of a seed value (truncation is a pure
 *      prefix cut, so this is the exact signature of an untouched row)
 *   C  the live value is plain prose and a prefix of the seed's `en` text —
 *      what happens when someone pastes the broken English off the rendered
 *      page back into the editor, losing the locale wrapper entirely
 *   B  the first run of real prose in the live value appears in exactly one
 *      seed value — catches rows the CMS re-saved wrapped in an extra
 *      {"en":"…"} envelope, or with the English shortened, where a straight
 *      prefix comparison no longer lines up
 *
 * A row is only ever considered when it is demonstrably damaged: JSON-shaped
 * but unparseable, or plain prose that is a strict prefix of the seed text.
 * Sitting on the old 191-character ceiling is NOT damage on its own — two rows
 * are exactly 191 characters and parse cleanly, and those are left alone.
 *
 * Run with no arguments for the report only; pass --apply to write.
 */
$apply = in_array('--apply', $argv, true);

$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$live = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', $options);
$seed = new PDO('mysql:host=127.0.0.1;dbname=nesim_restore;charset=utf8mb4', 'root', '', $options);

// Read the column list back out of the generated SQL so the widening and the
// restore cannot drift apart.
$sqlFile = dirname(__DIR__, 2) . '/database/widen-content-columns.sql';
$targets = [];

foreach (file($sqlFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (preg_match('/ALTER TABLE `(\w+)` MODIFY `(\w+)` TEXT/', $line, $m)) {
        $targets[] = [$m[1], $m[2]];
    }
}

/** True when a value looks like a stored document rather than free prose. */
function isJsonShaped(string $value): bool
{
    $trimmed = ltrim($value);

    return str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[');
}

function parses(string $value): bool
{
    json_decode($value);

    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Reduces a stored value to the prose it contains, so two versions of the same
 * text can be compared even when one of them has been wrapped in an extra
 * {"en":"…"} envelope by the CMS — once, or in one row's case, twice.
 *
 * Peeling envelopes with json_decode does not work here: the value is truncated,
 * so it never parses. Stripping the JSON punctuation outright does, because the
 * prose itself contains none of the characters being removed at the point where
 * the comparison is made.
 */
function proseHead(string $value): string
{
    $text = str_replace(['\\', '"', '{', '}', '[', ']', ':'], ' ', $value);
    $text = preg_replace('/\b(?:en|am|om)\b/u', ' ', $text) ?? $text;
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
}

/** The `en` string inside a locale document, or the value itself when it is prose. */
function englishOf(string $value): ?string
{
    $decoded = json_decode($value, true);

    if (is_array($decoded) && isset($decoded['en']) && is_string($decoded['en'])) {
        return $decoded['en'];
    }

    return isJsonShaped($value) ? null : $value;
}

echo $apply ? "== APPLYING ==\n" : "== DRY RUN ==\n";
echo count($targets), " columns to inspect\n\n";

$restored = [];
$unmatched = [];
$ambiguous = [];

foreach ($targets as [$table, $column]) {
    $hasSlug = (bool) $live->query(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = 'nesim' AND TABLE_NAME = " . $live->quote($table) . " AND COLUMN_NAME = 'slug'"
    )->fetchColumn();

    $select = '`id`' . ($hasSlug ? ', `slug`' : '') . ', `' . $column . '` AS v';

    $liveRows = $live->query("SELECT {$select} FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    $seedRows = $seed->query("SELECT {$select} FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

    if ($liveRows === [] || $seedRows === []) {
        continue;
    }

    foreach ($liveRows as $row) {
        $value = $row['v'];

        if ($value === null || $value === '') {
            continue;
        }

        $label = preg_replace(
            '/\s+/u',
            ' ',
            $table . '.' . $column . ' [' . ($row['slug'] ?? $row['id']) . ']'
        );

        /* ── Is it damaged at all? ─────────────────────────────────────────── */

        $head = proseHead($value);
        $damaged = false;

        if (isJsonShaped($value) && ! parses($value)) {
            $damaged = true;
        }

        if (! $damaged && ! isJsonShaped($value)) {
            // Prose is only damaged when it is a strict prefix of the original.
            foreach ($seedRows as $seedRow) {
                $original = englishOf((string) $seedRow['v'] ?? '');

                if ($original !== null && $original !== $value && str_starts_with($original, $value)) {
                    $damaged = true;
                    break;
                }
            }
        }

        if (! $damaged) {
            continue;
        }

        /* ── Pass A: the live value is a prefix of the seed value ──────────── */

        $candidates = [];

        foreach ($seedRows as $seedRow) {
            $original = $seedRow['v'];

            if ($original !== null && $original !== $value && str_starts_with($original, $value)) {
                $candidates[] = ['rule' => 'A', 'row' => $seedRow];
            }
        }

        /* ── Pass C: prose that is a prefix of the seed's English ──────────── */

        if ($candidates === [] && ! isJsonShaped($value)) {
            foreach ($seedRows as $seedRow) {
                $original = englishOf((string) $seedRow['v']);

                if ($original !== null && $original !== $value && str_starts_with($original, $value)) {
                    $candidates[] = ['rule' => 'C', 'row' => $seedRow];
                }
            }
        }

        /* ── Pass B: the prose head appears in exactly one seed value ──────── */

        if ($candidates === [] && mb_strlen($head) >= 30) {
            $needle = mb_substr($head, 0, 48);

            foreach ($seedRows as $seedRow) {
                if ($seedRow['v'] !== null && str_contains(proseHead((string) $seedRow['v']), $needle)) {
                    $candidates[] = ['rule' => 'B', 'row' => $seedRow];
                }
            }

            // A shared slug settles it when two records open the same way.
            if ($hasSlug && count($candidates) > 1) {
                $exact = array_values(array_filter(
                    $candidates,
                    static fn ($c) => $c['row']['slug'] === $row['slug']
                ));

                if (count($exact) === 1) {
                    $candidates = $exact;
                }
            }
        }

        if ($candidates === []) {
            $unmatched[] = $label;
            continue;
        }

        if (count($candidates) > 1) {
            $ambiguous[] = $label . ' (' . count($candidates) . ' candidates)';
            continue;
        }

        $original = $candidates[0]['row']['v'];

        $restored[] = [
            'label' => $label,
            'rule' => $candidates[0]['rule'],
            'from' => mb_strlen($value),
            'to' => mb_strlen($original),
            'table' => $table,
            'column' => $column,
            'id' => $row['id'],
            'value' => $original,
        ];
    }
}

foreach ($restored as $item) {
    echo str_pad($item['label'], 46), ' rule ', $item['rule'], '  ',
         str_pad((string) $item['from'], 5, ' ', STR_PAD_LEFT), ' -> ',
         str_pad((string) $item['to'], 5, ' ', STR_PAD_LEFT), ' chars', PHP_EOL;

    if ($apply) {
        $statement = $live->prepare(
            'UPDATE `' . $item['table'] . '` SET `' . $item['column'] . '` = ? WHERE `id` = ?'
        );
        $statement->execute([$item['value'], $item['id']]);
    }
}

echo PHP_EOL, 'restoring: ', count($restored), PHP_EOL;

if ($unmatched !== []) {
    echo 'NO SEED ORIGINAL (', count($unmatched), ') — left as-is:', PHP_EOL;
    foreach ($unmatched as $item) {
        echo '  ', $item, PHP_EOL;
    }
}

if ($ambiguous !== []) {
    echo 'AMBIGUOUS (', count($ambiguous), ') — left as-is:', PHP_EOL;
    foreach ($ambiguous as $item) {
        echo '  ', $item, PHP_EOL;
    }
}

if (! $apply) {
    echo PHP_EOL, 'dry run — nothing written. re-run with --apply.', PHP_EOL;
}
