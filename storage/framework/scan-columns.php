<?php

/*
 * Lists every varchar column that is not covered by an index, so the widening
 * can be aimed precisely. Indexed columns must keep their type: a UNIQUE slug
 * cannot become TEXT without a prefix length, and an InnoDB foreign key has to
 * match the column it points at.
 */
$pdo = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$indexed = [];
foreach ($pdo->query(
    'SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE()'
)->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $indexed[$row['TABLE_NAME'] . '.' . $row['COLUMN_NAME']] = true;
}

$columns = $pdo->query(
    "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND DATA_TYPE = 'varchar'
     ORDER BY TABLE_NAME, ORDINAL_POSITION"
)->fetchAll(PDO::FETCH_ASSOC);

$free = [];
$locked = [];

foreach ($columns as $column) {
    $key = $column['TABLE_NAME'] . '.' . $column['COLUMN_NAME'];
    $line = sprintf('%-40s %s', $key, $column['IS_NULLABLE'] === 'YES' ? 'NULL' : 'NOT NULL');

    if (isset($indexed[$key])) {
        $locked[] = $line;
    } else {
        $free[] = $line;
    }
}

echo '== INDEXED (must stay varchar) : ', count($locked), ' ==', PHP_EOL;
echo '  ', implode(PHP_EOL . '  ', $locked), PHP_EOL;

echo PHP_EOL, '== UNINDEXED (safe to widen) : ', count($free), ' ==', PHP_EOL;
echo '  ', implode(PHP_EOL . '  ', $free), PHP_EOL;
