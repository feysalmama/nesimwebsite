<?php

/*
 * Runs laravel/database/widen-content-columns.sql against `nesim`, one statement
 * at a time, so a failure names the column instead of aborting half a batch.
 *
 * Every statement is a widening (varchar(191) -> TEXT), which MySQL performs in
 * place without touching existing values, so nothing here can lose data.
 */
$root = dirname(__DIR__, 3);
$file = $root . '/laravel/database/widen-content-columns.sql';

if (! is_file($file)) {
    fwrite(STDERR, "missing: {$file}\n");
    exit(1);
}

$pdo = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$statements = [];

foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '--')) {
        continue;
    }

    $statements[] = $line;
}

echo 'running ', count($statements), ' statements', PHP_EOL;

$done = 0;

foreach ($statements as $statement) {
    try {
        $pdo->exec($statement);
        $done++;
    } catch (Throwable $e) {
        fwrite(STDERR, 'FAILED: ' . $statement . PHP_EOL . '  ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}

echo $done, ' ok', PHP_EOL;

// Confirm the columns really are TEXT now.
$remaining = $pdo->query(
    "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = 'nesim' AND DATA_TYPE = 'varchar'
       AND CHARACTER_MAXIMUM_LENGTH = 191
     ORDER BY TABLE_NAME, ORDINAL_POSITION"
)->fetchAll(PDO::FETCH_ASSOC);

echo 'varchar(191) columns still present: ', count($remaining), PHP_EOL;
