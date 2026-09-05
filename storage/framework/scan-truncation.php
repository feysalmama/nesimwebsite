<?php

/*
 * Finds every column that MySQL has silently truncated.
 *
 * Two symptoms, either of which is enough:
 *   - a value sitting exactly at the column's character ceiling
 *   - a value that opens like JSON but no longer parses, which is what happens
 *     to a locale column ({"en":…,"am":…,"om":…}) cut mid-string
 *
 * Indexes are reported too: a UNIQUE slug column cannot simply become TEXT, so
 * the fix has to know which columns are safe to widen.
 */
$pdo = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$columns = $pdo->query(
    "SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND DATA_TYPE IN ('varchar', 'char')
       AND CHARACTER_MAXIMUM_LENGTH IS NOT NULL
     ORDER BY TABLE_NAME, ORDINAL_POSITION"
)->fetchAll(PDO::FETCH_ASSOC);

$indexed = [];
foreach ($pdo->query(
    "SELECT TABLE_NAME, COLUMN_NAME, NON_UNIQUE
     FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()"
)->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $indexed[$row['TABLE_NAME'] . '.' . $row['COLUMN_NAME']] = $row['NON_UNIQUE'] == 0 ? 'UNIQUE' : 'index';
}

$atCeiling = [];
$brokenJson = [];

foreach ($columns as $column) {
    $table = $column['TABLE_NAME'];
    $name = $column['COLUMN_NAME'];
    $max = (int) $column['CHARACTER_MAXIMUM_LENGTH'];
    $quoted = $table . '.' . $name;

    $full = $pdo->query("SELECT CHAR_LENGTH(`$name`) FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);

    $hits = count(array_filter($full, static fn ($length) => (int) $length === $max));

    if ($hits > 0) {
        $atCeiling[] = sprintf('%-34s varchar(%d)  %d row(s) at the ceiling  %s', $quoted, $max, $hits, $indexed[$quoted] ?? '');
    }

    // Only worth parsing on columns wide enough to hold a truncated document.
    $rows = $pdo->query("SELECT `$name` FROM `$table` WHERE `$name` LIKE '{%' OR `$name` LIKE '[%'")->fetchAll(PDO::FETCH_COLUMN);

    $broken = 0;
    foreach ($rows as $value) {
        if (is_string($value) && $value !== '' && json_decode($value) === null) {
            $broken++;
        }
    }

    if ($broken > 0) {
        $brokenJson[] = sprintf('%-34s varchar(%d)  %d row(s) unparseable      %s', $quoted, $max, $broken, $indexed[$quoted] ?? '');
    }
}

echo '== VALUES SITTING EXACTLY AT THEIR COLUMN CEILING ==', PHP_EOL;

if ($atCeiling === []) {
    echo '  (none)', PHP_EOL;
} else {
    echo '  ', implode(PHP_EOL . '  ', $atCeiling), PHP_EOL;
}

echo PHP_EOL, '== VALUES THAT LOOK LIKE JSON BUT NO LONGER PARSE ==', PHP_EOL;

if ($brokenJson === []) {
    echo '  (none)', PHP_EOL;
} else {
    echo '  ', implode(PHP_EOL . '  ', $brokenJson), PHP_EOL;
}
