<?php

/*
 * Why did some truncated rows fail to match a seed original? Prints the live
 * value next to every seed value for the same column so the mismatch is visible
 * rather than guessed at.
 */
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$live = new PDO('mysql:host=127.0.0.1;dbname=nesim;charset=utf8mb4', 'root', '', $options);
$seed = new PDO('mysql:host=127.0.0.1;dbname=nesim_restore;charset=utf8mb4', 'root', '', $options);

$cases = [
    ['presidentmessage', 'message', null],
    ['heroslide', 'subtitle', 'cmtnd21s00003583na9rwsshq'],
    ['newspost', 'excerpt', 'cmtnd21tm001b583n3cgbc7g9'],
    ['program', 'body', 'cmtnd21sa000b583nuqrxu1wj'],
    ['project', 'summary', 'cmtnd21sm000h583nq70tal8b'],
    ['service', 'summary', 'cmtnd21sp000l583n3aro45ry'],
    ['testimonial', 'quote', 'cmtnd21tb000v583nqr6u9my9'],
];

foreach ($cases as [$table, $column, $id]) {
    echo str_repeat('=', 78), PHP_EOL;
    echo $table, '.', $column, $id === null ? '' : '  [' . $id . ']', PHP_EOL;

    $hasSlug = (bool) $seed->query(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = 'nesim_restore' AND TABLE_NAME = " . $seed->quote($table) . " AND COLUMN_NAME = 'slug'"
    )->fetchColumn();

    $where = $id === null ? '' : ' WHERE `id` = ' . $live->quote($id);
    $rows = $live->query("SELECT `id`, `{$column}` AS v FROM `{$table}`{$where}")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $v = $row['v'];
        echo '  LIVE  chars=', $v === null ? 'NULL' : mb_strlen($v),
             ' bytes=', $v === null ? 0 : strlen($v),
             ' utf8=', ($v === null || mb_check_encoding($v, 'UTF-8')) ? 'ok' : 'BROKEN', PHP_EOL;
        echo '        ', $v === null ? '' : mb_substr($v, 0, 200), PHP_EOL;
    }

    $key = $hasSlug ? '`slug`' : '`id`';
    $seedRows = $seed->query("SELECT `id`, {$key} AS k, `{$column}` AS v FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

    echo '  -- seed rows: ', count($seedRows), PHP_EOL;

    foreach ($seedRows as $row) {
        $v = $row['v'];
        echo '  SEED  ', str_pad((string) $row['k'], 34),
             ' chars=', $v === null ? 'NULL' : mb_strlen($v), PHP_EOL;
        echo '        ', $v === null ? '' : mb_substr($v, 0, 130), PHP_EOL;
    }

    echo PHP_EOL;
}
