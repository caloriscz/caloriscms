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
// Web requests get debug mode (Tracy bar and bluescreen) only from the addresses listed in the
// optional, untracked app/config/debug-ips.local.php, e.g. `<?php return ['203.0.113.10'];`,
// so no address is published with the code. Without that file the web runs in production mode.
$debugIpsFile = __DIR__ . '/config/debug-ips.local.php';
$debugIps = is_file($debugIpsFile) ? array_values(array_filter((array) require $debugIpsFile,
    static function ($ip): bool {
        return \is_string($ip) && $ip !== '';
    })) : [];
$configurator->setDebugMode(PHP_SAPI === 'cli'
    || ($debugIps !== [] && Nette\Configurator::detectDebugMode($debugIps)));
$configurator->enableDebugger($logDir);
$configurator->setTempDirectory($tempDir);

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
