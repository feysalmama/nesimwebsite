<?php

// Is the 191-character ceiling already truncating live content?
$pdo = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

foreach (['timelineData', 'missionText', 'visionText', 'valuesText', 'storyBody'] as $column) {
    $value = (string) $pdo->query('SELECT ' . $column . ' FROM aboutcontent')->fetchColumn();

    echo str_repeat('=', 20), ' ', $column, ' (', strlen($value), ' bytes / ',
        mb_strlen($value), ' chars)', PHP_EOL;
    echo '  valid JSON: ', json_decode($value) === null && $value !== '' ? 'NO' : 'yes/n-a', PHP_EOL;
    echo '  tail: ...', mb_substr($value, -60), PHP_EOL, PHP_EOL;
}

$message = (string) $pdo->query('SELECT message FROM presidentmessage')->fetchColumn();
echo str_repeat('=', 20), ' presidentmessage.message (', mb_strlen($message), ' chars)', PHP_EOL;
echo '  tail: ...', mb_substr($message, -80), PHP_EOL;
