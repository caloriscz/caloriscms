<?php
declare(strict_types=1);

define('APP_DIR', __DIR__ . '/www');

$container = require __DIR__ . '/app/bootstrap.php';
$container->getByType(Contributte\Console\Application::class)->run();
