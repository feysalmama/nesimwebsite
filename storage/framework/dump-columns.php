<?php

// Reads the real column types so the editor's validation limits are not guesses.
$pdo = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$tables = ['aboutcontent', 'landingcontent', 'presidentmessage', 'globalsettings', 'media', 'activitylog', 'users'];

foreach ($tables as $table) {
    echo str_repeat('=', 20), ' ', $table, PHP_EOL;

    $rows = $pdo->query(
        'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ' . $pdo->quote($table) . '
         ORDER BY ORDINAL_POSITION'
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        printf("  %-22s %-16s %s\n", $row['COLUMN_NAME'], $row['COLUMN_TYPE'], $row['IS_NULLABLE']);
    }
}
