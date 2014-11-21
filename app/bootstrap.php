<?php

require __DIR__ . '/../vendor/Nette/loader.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode('94.74.253.47'); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');
error_reporting(E_ALL ^ E_NOTICE);

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
