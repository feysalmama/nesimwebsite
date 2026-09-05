<?php

// Temporary probe: are the aboutcontent values that sit at exactly 191
// characters truncated mid-sentence, and is timelineData still valid JSON?
// Deleted straight after.
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$about = DB::table('aboutcontent')->first();

foreach (['missionText', 'visionText', 'valuesText', 'storyBody', 'timelineData'] as $column) {
    $value = (string) $about->{$column};

    echo "== {$column} (".mb_strlen($value)." chars)\n";
    echo '   head: '.mb_substr($value, 0, 60)."\n";
    echo '   tail: '.mb_substr($value, -60)."\n";

    if ($column === 'timelineData') {
        $decoded = json_decode($value, true);

        echo '   json_last_error: '.json_last_error_msg()."\n";
        echo '   decoded: '.var_export(is_array($decoded) ? count($decoded).' entries' : $decoded, true)."\n";
    }

    echo PHP_EOL;
}

$president = DB::table('presidentmessage')->first();

echo "== presidentmessage.message (".mb_strlen((string) $president->message)." chars)\n";
echo '   tail: '.mb_substr((string) $president->message, -80)."\n";
