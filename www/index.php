<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

define('APP_DIR', dirname(__FILE__));

$container = require __DIR__ . '/../app/bootstrap.php';

$container->getService('application')->run();
