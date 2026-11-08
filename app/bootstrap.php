<?php

require __DIR__ . '/../vendor/autoload.php';

$wwwDir = __DIR__ . '/../www';
$logDir = $wwwDir . '/log';
$tempDir = $wwwDir . '/temp';
$sessionDir = $tempDir . '/sessions';

foreach ([$logDir, $tempDir . '/cache', $sessionDir] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

\Tracy\Debugger::setSessionStorage(new \Tracy\FileSession($sessionDir));
\Tracy\Debugger::$keysToHide = array_merge(\Tracy\Debugger::$keysToHide,
    ['resetToken', 'reset_token_hash', 'password', 'password1', 'password2', '_token_']);

$configurator = new Nette\Configurator;
$configurator->setDebugMode(PHP_SAPI === 'cli');
$configurator->enableDebugger($logDir);
$configurator->setTempDirectory($tempDir);

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
