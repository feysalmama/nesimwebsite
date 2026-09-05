<?php

/*
 * Removes the scratch database the restore read its originals from. Safe to run
 * at any point: it only ever touches `nesim_restore`, never `nesim`.
 */
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec('DROP DATABASE IF EXISTS nesim_restore');

$databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);

echo 'nesim_restore dropped. remaining: ', implode(', ', $databases), PHP_EOL;
