<?php
declare(strict_types=1);

// Characterizes unused legacy helpers. This is not a passing regression contract.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/model/IO.php';

use App\Model\IO;

$root = sys_get_temp_dir() . '/caloris-io-audit-' . bin2hex(random_bytes(8));
mkdir($root);
$results = [];
try {
    file_put_contents($root . '/source', 'source');
    IO::rename($root . '/source', $root . '/destination');
    $results['rename_existing_source_missing_destination'] = [
        'source_exists' => file_exists($root . '/source'),
        'destination_exists' => file_exists($root . '/destination'),
    ];
    file_put_contents($root . '/destination', 'keep');
    IO::rename($root . '/source', $root . '/destination');
    $results['rename_existing_destination'] = file_get_contents($root . '/destination');
    IO::rename($root . '/missing', $root . '/also-missing');
    $results['rename_both_missing'] = file_exists($root . '/also-missing');
    $warnings = [];
    set_error_handler(static function ($severity, $message) use (&$warnings): bool {
        $warnings[] = $message;
        return true;
    });
    try {
        IO::rename($root . '/missing', $root . '/destination');
    } finally {
        restore_error_handler();
    }
    $results['rename_missing_source_existing_destination'] = [
        'warning_count' => count($warnings),
        'destination' => file_get_contents($root . '/destination'),
    ];
    mkdir($root . '/sizes');
    $results['empty_size'] = IO::folderSize($root . '/sizes');
    file_put_contents($root . '/sizes/a', 'abc');
    $results['flat_size'] = IO::folderSize($root . '/sizes');
    mkdir($root . '/sizes/nested');
    file_put_contents($root . '/sizes/nested/b', '12345');
    try {
        $results['recursive_size'] = IO::folderSize($root . '/sizes');
    } catch (\Error $e) {
        $results['recursive_error'] = $e->getMessage();
    }
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} finally {
    // Only remove the fixture paths created above; never recurse through user data.
    foreach (['sizes/nested/b', 'sizes/a', 'source', 'destination'] as $file) {
        if (is_file($root . '/' . $file)) {
            unlink($root . '/' . $file);
        }
    }
    foreach (['sizes/nested', 'sizes', ''] as $directory) {
        if (is_dir($root . '/' . $directory)) {
            rmdir($root . '/' . $directory);
        }
    }
}
