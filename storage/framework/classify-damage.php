<?php

/*
 * Which of the damaged tables can be matched by slug, and which of the live
 * values actually still parse? Decides how the restore matcher has to work.
 */
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$live = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', $options);
$seed = new PDO('mysql:host=127.0.0.1;dbname=nesim_restore;charset=utf8mb4', 'root', '', $options);

$sqlFile = dirname(__DIR__, 2) . '/database/widen-content-columns.sql';
$targets = [];

foreach (file($sqlFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (preg_match('/ALTER TABLE `(\w+)` MODIFY `(\w+)` TEXT/', $line, $m)) {
        $targets[] = [$m[1], $m[2]];
    }
}

$hasSlug = [];

foreach ($live->query(
    "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = 'nesim' AND COLUMN_NAME = 'slug'"
)->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $hasSlug[$table] = true;
}

echo str_pad('table.column', 40), ' slug  broken-live-rows', PHP_EOL;

foreach ($targets as [$table, $column]) {
    $rows = $live->query("SELECT `{$column}` AS v FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
    $broken = 0;
    $ceiling = 0;

    foreach ($rows as $v) {
        if ($v === null || $v === '') {
            continue;
        }

        $t = ltrim($v);
        $jsonShaped = str_starts_with($t, '{') || str_starts_with($t, '[');

        if ($jsonShaped) {
            json_decode($v);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $broken++;
            }
        } elseif (mb_strlen($v) === 191) {
            // Plain prose sitting exactly on the old ceiling.
            $ceiling++;
        }
    }

    if ($broken === 0 && $ceiling === 0) {
        continue;
    }

    echo str_pad($table . '.' . $column, 40),
         isset($hasSlug[$table]) ? ' yes  ' : ' NO   ',
         'unparseable=', $broken, ' prose@191=', $ceiling, PHP_EOL;
}

echo PHP_EOL, '-- rows that are exactly 191 chars but still parse (false positives) --', PHP_EOL;

foreach ($targets as [$table, $column]) {
    $rows = $live->query("SELECT `id`, `{$column}` AS v FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $v = $row['v'];

        if ($v === null || mb_strlen($v) !== 191) {
            continue;
        }

        $t = ltrim($v);

        if (! str_starts_with($t, '{') && ! str_starts_with($t, '[')) {
            continue;
        }

        json_decode($v);

        if (json_last_error() === JSON_ERROR_NONE) {
            echo '  ', $table, '.', $column, ' [', $row['id'], '] parses cleanly', PHP_EOL;
        }
    }
}
