<?php

/*
 * Creates (or recreates) the scratch database the restore reads its full-length
 * originals from. `nesim` itself is never touched by this script.
 */
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec('DROP DATABASE IF EXISTS nesim_restore');
$pdo->exec('CREATE DATABASE nesim_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

echo 'nesim_restore ready', PHP_EOL;
