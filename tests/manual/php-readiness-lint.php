<?php
declare(strict_types=1);
// No application boot or local configuration. Run in disposable candidate image.
$count = 0;
$failures = 0;
foreach (['app', 'tests'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,
        FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!in_array($file->getExtension(), ['php', 'phpt'], true)) { continue; }
        $output = [];
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $status);
        $count++;
        if ($status !== 0) {
            echo implode("\n", $output) . "\n";
            $failures++;
        }
    }
}
echo PHP_VERSION . ': ' . $count . ' files, ' . $failures . " lint failures\n";
exit($failures ? 1 : 0);
