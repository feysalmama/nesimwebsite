<?php

/*
 * Prints every spec class in app/Admin/Specs against the table it reads, the
 * component it renders and the roles it gates on — so the AdminNav slug map can
 * be checked against the classes rather than against memory.
 */
require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$files = glob(__DIR__ . '/../../app/Admin/Specs/*.php');

printf("%-30s %-28s %-22s %s\n", 'SPEC', 'TITLE', 'TABLE', 'COMPONENT');
echo str_repeat('-', 108), PHP_EOL;

$abstract = [];

foreach ($files as $file) {
    $class = 'App\\Admin\\Specs\\' . basename($file, '.php');

    if (! class_exists($class)) {
        echo 'MISSING CLASS ', $class, PHP_EOL;
        continue;
    }

    $reflection = new ReflectionClass($class);

    if ($reflection->isAbstract()) {
        $abstract[] = $reflection->getShortName();
        continue;
    }

    $spec = new $class;
    $model = $spec->model();

    printf(
        "%-30s %-28s %-22s %s\n",
        $reflection->getShortName(),
        $spec->title(),
        (new $model)->getTable(),
        $spec->component()
    );
}

echo PHP_EOL, 'abstract (shared bases): ', implode(', ', $abstract), PHP_EOL;
echo 'concrete specs: ', count($files) - count($abstract), PHP_EOL;
