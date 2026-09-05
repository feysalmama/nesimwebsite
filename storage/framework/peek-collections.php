<?php

/**
 * How many rows the live landing-content collections already hold, and how many
 * each spec allows. AdminPanelTest grows these collections, so it has to aim at
 * the index a new row actually lands on rather than assume the list is empty.
 *
 * php storage/framework/peek-collections.php
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$row = App\Models\LandingContent::query()->find(App\Models\LandingContent::SINGLETON_ID);

if ($row === null) {
    echo 'no landing-content row', PHP_EOL;

    return;
}

$spec = new App\Admin\Specs\LandingContentSpec;

foreach ($spec->sections() as $section) {
    foreach ($section['fields'] as $field) {
        if (! in_array($field['type'], ['repeater', 'imageList'], true)) {
            continue;
        }

        $value = json_decode((string) $row->{$field['name']}, true);
        $count = is_array($value) ? count($value) : 0;

        printf(
            "%-18s %-10s now %-3d max %-3d %s%s",
            $field['name'],
            $field['type'],
            $count,
            $field['max'] ?? '-',
            $count >= (int) ($field['max'] ?? PHP_INT_MAX) ? 'FULL - addRow refuses' : 'room to add',
            PHP_EOL,
        );
    }
}
