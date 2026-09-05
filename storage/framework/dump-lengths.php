<?php

// How close is the existing content to the varchar(191) ceiling?
$pdo = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo 'sql_mode = ', $pdo->query('SELECT @@sql_mode')->fetchColumn(), PHP_EOL, PHP_EOL;

$checks = [
    'presidentmessage' => ['name', 'position', 'message'],
    'aboutcontent' => ['storyBody', 'missionText', 'visionText', 'valuesText', 'timelineData'],
    'globalsettings' => ['address', 'footerText', 'seoDescription', 'tagline'],
];

foreach ($checks as $table => $columns) {
    echo str_repeat('=', 20), ' ', $table, PHP_EOL;

    foreach ($columns as $column) {
        $sql = 'SELECT CHAR_LENGTH(' . $column . ') FROM ' . $table;
        $lengths = array_map('intval', $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN));

        printf("  %-18s rows=%d  max=%d%s\n", $column, count($lengths), max($lengths) ?: 0,
            (max($lengths) ?: 0) >= 191 ? '   <-- AT THE CEILING' : '');
    }
}
