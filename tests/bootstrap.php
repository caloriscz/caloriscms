<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();

$loader = new Nette\Loaders\RobotLoader();
$loader->addDirectory(__DIR__ . '/../app');
$loader->setTempDirectory(sys_get_temp_dir() . '/caloriscms-tests-' . md5(__DIR__));
$loader->register();
