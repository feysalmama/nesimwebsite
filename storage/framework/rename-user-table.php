<?php

/**
 * Renames the staff-account table `user` -> `users`, Laravel's convention.
 *
 * Idempotent and verified rather than a bare RENAME: the table holds the only
 * login to the panel and three child tables point at it through real InnoDB
 * foreign keys, so this reads the before-state, renames, then proves nothing
 * was lost. DDL auto-commits in MySQL, so there is no transaction to hide
 * behind - the before/after comparison is the safety net instead.
 *
 *   C:\xampp\php\php.exe storage\framework\rename-user-table.php
 *
 * To undo: RENAME TABLE `users` TO `user`;
 */

use Illuminate\Support\Facades\DB;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

const FROM = 'user';

const TO = 'users';

function tableExists(string $name): bool
{
    return DB::selectOne('SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$name])->n > 0;
}

/** @return array<int, string> */
function columnNames(string $table): array
{
    return array_map(
        static fn (object $row): string => $row->Field,
        DB::select('SHOW COLUMNS FROM `'.$table.'`'),
    );
}

/** @return array<int, object> */
function foreignKeysTo(string $table): array
{
    return DB::select(
        'SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
         FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = ?
         ORDER BY TABLE_NAME, COLUMN_NAME',
        [$table],
    );
}

echo "=== before ===\n";

if (! tableExists(FROM) && tableExists(TO)) {
    echo "Nothing to do: `".FROM.'` is already gone and `'.TO."` exists.\n";

    foreach (foreignKeysTo(TO) as $fk) {
        echo "  {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> ".TO." ({$fk->CONSTRAINT_NAME})\n";
    }

    exit(0);
}

if (! tableExists(FROM)) {
    fwrite(STDERR, "Aborted: neither `".FROM.'` nor `'.TO."` exists.\n");

    exit(1);
}

if (tableExists(TO)) {
    fwrite(STDERR, "Aborted: `".FROM.'` and `'.TO."` both exist. Resolve by hand - refusing to guess which holds the accounts.\n");

    exit(1);
}

$beforeRows = DB::table(FROM)->count();
$beforeColumns = columnNames(FROM);
$beforeKeys = foreignKeysTo(FROM);
$beforeHash = DB::selectOne("SELECT BIT_XOR(CRC32(CONCAT_WS('|', id, name, email, passwordHash, role))) AS h FROM `".FROM.'`')->h;

echo '  rows: '.$beforeRows."\n";
echo '  columns: '.implode(', ', $beforeColumns)."\n";
echo '  inbound foreign keys: '.count($beforeKeys)."\n";

foreach ($beforeKeys as $fk) {
    echo "    {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} ({$fk->CONSTRAINT_NAME})\n";
}

$triggers = DB::select(
    "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS
     WHERE TRIGGER_SCHEMA = DATABASE() AND (ACTION_STATEMENT LIKE '%`".FROM."`%' OR ACTION_STATEMENT LIKE '% ".FROM." %')",
);

if ($triggers !== []) {
    fwrite(STDERR, "Aborted: a trigger references `".FROM.'` and would not follow the rename: '.implode(', ', array_column($triggers, 'TRIGGER_NAME'))."\n");

    exit(1);
}

echo "\n=== rename ===\n";

DB::statement('RENAME TABLE `'.FROM.'` TO `'.TO.'`');

echo '  RENAME TABLE `'.FROM.'` TO `'.TO."`\n";

echo "\n=== after ===\n";

$afterRows = DB::table(TO)->count();
$afterColumns = columnNames(TO);
$afterHash = DB::selectOne("SELECT BIT_XOR(CRC32(CONCAT_WS('|', id, name, email, passwordHash, role))) AS h FROM `".TO.'`')->h;

$failures = [];

if ($afterRows !== $beforeRows) {
    $failures[] = 'row count changed: '.$beforeRows.' -> '.$afterRows;
}

if ($afterColumns !== $beforeColumns) {
    $failures[] = 'columns changed: '.implode(',', $beforeColumns).' -> '.implode(',', $afterColumns);
}

if ($afterHash !== $beforeHash) {
    $failures[] = 'content checksum changed';
}

if (tableExists(FROM)) {
    $failures[] = '`'.FROM.'` still exists';
}

echo '  rows: '.$afterRows."\n";
echo '  columns: '.implode(', ', $afterColumns)."\n";

// InnoDB rewrites child foreign keys to follow a renamed parent, but that is
// exactly the kind of thing worth proving rather than assuming.
$afterKeys = foreignKeysTo(TO);
$orphans = foreignKeysTo(FROM);

echo '  inbound foreign keys now pointing at `'.TO.'`: '.count($afterKeys)."\n";

foreach ($afterKeys as $fk) {
    echo "    {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} ({$fk->CONSTRAINT_NAME})\n";
}

if (count($afterKeys) !== count($beforeKeys)) {
    $failures[] = 'foreign keys did not all follow: '.count($beforeKeys).' -> '.count($afterKeys);
}

if ($orphans !== []) {
    $failures[] = 'still pointing at the dead name `'.FROM.'`: '.implode(', ', array_map(static fn (object $fk): string => $fk->TABLE_NAME.'.'.$fk->COLUMN_NAME, $orphans));
}

echo "\n";

if ($failures !== []) {
    fwrite(STDERR, "FAILED:\n");

    foreach ($failures as $failure) {
        fwrite(STDERR, '  - '.$failure."\n");
    }

    fwrite(STDERR, "\nUndo with: RENAME TABLE `".TO.'` TO `'.FROM."`;\n");

    exit(1);
}

echo "OK: `".FROM.'` -> `'.TO.'`, '.$afterRows.' row(s) and '.count($afterKeys)." foreign key(s) intact.\n";
