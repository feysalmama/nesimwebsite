<?php

/*
 * Feeds make-widen.js: "table.column" => is-nullable, for every varchar column
 * in the schema. ALTER ... MODIFY restates the whole definition, so omitting
 * NOT NULL would silently make a required column optional.
 */
$pdo = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$rows = $pdo->query(
    "SELECT TABLE_NAME, COLUMN_NAME, IS_NULLABLE, COLUMN_TYPE
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND DATA_TYPE = 'varchar'"
)->fetchAll(PDO::FETCH_ASSOC);

$map = [];

foreach ($rows as $row) {
    $map[$row['TABLE_NAME'] . '.' . $row['COLUMN_NAME']] = $row['IS_NULLABLE'] === 'YES';
}

$out = __DIR__ . '/nullable.json';

file_put_contents($out, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

echo 'wrote ', count($map), ' varchar columns to ', $out, PHP_EOL;
