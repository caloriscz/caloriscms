<?php

require __DIR__ . '/../vendor/autoload.php';
require_once '../vendor/ezyang/htmlpurifier/library/HTMLPurifier.php';

$configurator = new Nette\Configurator;
$configurator->setDebugMode(array("0.0.0.0")); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');
error_reporting(E_ALL ^ E_NOTICE);
\Tracy\Debugger::$strictMode = FALSE;
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
