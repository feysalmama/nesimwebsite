<?php

/*
 * Dumps the columns of every table the public pages read or write, so the port
 * inserts into columns that exist and filters on the ones the React app used.
 * Read-only: SHOW COLUMNS only, nothing is written.
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = [
    'program', 'service', 'project', 'projectimage', 'newspost', 'blogpost',
    'gallery', 'galleryimage', 'resource', 'testimonial', 'teammember',
    'faqitem', 'membershipcategory', 'impactstat', 'presidentmessage',
    'contactmessage', 'donationintent', 'volunteerapplication',
    'registrationsubmission', 'membershipapplication', 'tag', '_blogposttotag',
];

foreach ($tables as $table) {
    $columns = DB::select('SHOW COLUMNS FROM `'.$table.'`');

    echo str_repeat('=', 72), PHP_EOL, $table, PHP_EOL;

    foreach ($columns as $column) {
        $notes = [];

        if ($column->Null === 'NO' && $column->Default === null && $column->Extra !== 'auto_increment') {
            $notes[] = 'REQUIRED';
        }

        if ($column->Default !== null) {
            $notes[] = 'default='.$column->Default;
        }

        if ($column->Extra !== '') {
            $notes[] = $column->Extra;
        }

        printf(
            "  %-22s %-18s %s%s\n",
            $column->Field,
            $column->Type,
            $column->Null === 'NO' ? 'NOT NULL' : 'null    ',
            $notes === [] ? '' : '  ['.implode(', ', $notes).']'
        );
    }
}

echo str_repeat('=', 72), PHP_EOL, 'row counts', PHP_EOL;

foreach ($tables as $table) {
    printf("  %-24s %d\n", $table, DB::table($table)->count());
}
