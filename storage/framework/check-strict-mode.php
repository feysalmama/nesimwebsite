<?php

/*
 * Confirms the Laravel connection really runs in strict mode. XAMPP's server
 * default has no STRICT_TRANS_TABLES, which is why Prisma's writes truncated
 * silently; config/database.php sets 'strict' => true, and this checks that it
 * reaches the session rather than trusting the config file to be honoured.
 *
 * Run through `artisan tinker --execute` style bootstrap: php artisan db:strict
 * is not a real command, so this boots the framework directly.
 */
require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$mode = Illuminate\Support\Facades\DB::selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;

echo 'session sql_mode: ', $mode, PHP_EOL;
echo 'STRICT_TRANS_TABLES: ', str_contains($mode, 'STRICT_TRANS_TABLES') ? 'ON' : 'OFF', PHP_EOL;

// A 300-character value into a column that is still varchar(191) must throw
// now, not quietly shorten. `media`.`altText` was widened, so use a column that
// was deliberately left alone: `user`.`name`.
try {
    Illuminate\Support\Facades\DB::transaction(function () {
        Illuminate\Support\Facades\DB::table('user')->insert([
            'id' => 'strict-mode-probe',
            'name' => str_repeat('x', 300),
            'email' => 'strict-probe@example.test',
            'passwordHash' => 'x',
            'role' => 'VIEWER',
            'createdAt' => now(),
        ]);
    });

    $length = Illuminate\Support\Facades\DB::table('user')
        ->where('id', 'strict-mode-probe')->value('name');

    echo 'PROBLEM: insert succeeded, stored ', mb_strlen((string) $length), ' of 300 chars', PHP_EOL;
} catch (Throwable $e) {
    // Only a length error proves the point; anything else means the probe itself
    // is wrong and says nothing about sql_mode.
    $message = $e->getMessage();

    if (str_contains($message, 'Data too long') || str_contains($message, '1406')) {
        echo 'insert correctly refused: data too long for the column', PHP_EOL;
    } else {
        echo 'PROBE BROKEN (not a length error): ', $message, PHP_EOL;
    }
} finally {
    Illuminate\Support\Facades\DB::table('user')->where('id', 'strict-mode-probe')->delete();
}
