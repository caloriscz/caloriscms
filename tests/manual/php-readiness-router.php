<?php
// PHP built-in server only: forward virtual XML routes like Apache rewrite does.
$public = realpath(__DIR__ . '/../../www');
$path = realpath($public . rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
if ($path && strpos($path, $public . DIRECTORY_SEPARATOR) === 0 && is_file($path)) {
    return false;
}
require $public . '/index.php';
