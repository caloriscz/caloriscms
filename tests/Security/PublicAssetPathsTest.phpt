<?php
declare(strict_types=1);

// Taskino 48cf8bfd: some hosts deny every URL with a /vendor/ segment, so the File
// Manager's 2.1.70 CSS, images, sounds and workers never loaded there. Public assets
// live in www/elfinder, and nothing points the browser at a /vendor/ URL.

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$root = __DIR__ . '/../..';
Assert::false(is_dir($root . '/www/vendor'));
foreach (['css/elfinder.min.css', 'css/theme.css', 'img/icons-big.svg', 'sounds/rm.wav', 'js/worker/calcfilehash.js'] as $asset) {
    Assert::true(is_file($root . '/www/elfinder/' . $asset), $asset);
}

$layout = file_get_contents($root . '/app/AdminModule/templates/@layout.latte');
Assert::contains('href="/elfinder/css/elfinder.min.css?v={$adminBundleVersion}"', $layout);
Assert::contains("baseUrl: '/elfinder/',", $layout);
Assert::contains("path.join(root, 'www/elfinder')", file_get_contents($root . '/tools/build/elfinder.cjs'));

$offenders = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app', FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if (in_array($file->getExtension(), ['latte', 'php'], true)
        && preg_match('~["\'(]/vendor/~', file_get_contents($file->getPathname()))) {
        $offenders[] = substr($file->getPathname(), strlen($root) + 1);
    }
}
foreach (['www/js/caloris-admin.js', 'www/js/all-front-custom.js'] as $script) {
    if (preg_match('~["\'(]/vendor/~', file_get_contents($root . '/' . $script))) {
        $offenders[] = $script;
    }
}
Assert::same([], $offenders);
