<?php
declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

// A real web SAPI is essential: Nette deliberately bypasses checkHeaders in CLI.
$dir = sys_get_temp_dir() . '/caloriscms-error-http-' . bin2hex(random_bytes(8));
mkdir($dir, 0700);
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
Assert::type('resource', $socket);
$address = stream_socket_get_name($socket, false);
fclose($socket);
$env = getenv();
$env['CALORIS_ERROR_HTTP_FIXTURE_DIR'] = $dir;
$process = proc_open([
    PHP_BINARY, '-d', 'output_buffering=0', '-d', 'display_errors=0',
    '-d', 'log_errors=1', '-S', $address, __DIR__ . '/../fixtures/error-responses-router.php',
], [0 => ['pipe', 'r'], 1 => ['file', $dir . '/server.log', 'a'], 2 => ['file', $dir . '/server.log', 'a']], $pipes, null, $env);
Assert::type('resource', $process);
fclose($pipes[0]);

$get = static function (string $path, bool $ajax = false) use ($address): array {
    $context = stream_context_create(['http' => [
        'ignore_errors' => true, 'timeout' => 5,
        'header' => $ajax ? 'X-Requested-With: XMLHttpRequest' : '',
    ]]);
    $body = file_get_contents('http://' . $address . $path, false, $context);
    return [$body, implode("\n", $http_response_header)];
};
try {
    $ready = false;
    for ($attempt = 0; $attempt < 100; $attempt++) {
        $probe = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
        if ($probe) { fclose($probe); $ready = true; break; }
        usleep(50000);
    }
    Assert::true($ready, 'Isolated PHP HTTP server started');
    Assert::same('ready', $get('/health')[0]);
    foreach ([false, true] as $late) {
        foreach ([400, 403, 404, 405, 410, 500] as $status) {
            foreach ([false, true] as $ajax) {
                [$body, $headers] = $get('/?status=' . $status . '&late=' . (int) $late, $ajax);
                Assert::true(is_file($dir . '/result.json'), file_get_contents($dir . '/server.log'));
                $result = json_decode(file_get_contents($dir . '/result.json'), true);
                Assert::notContains('SECONDARY-ERROR', $body);
                Assert::notContains('private-', $body);
                Assert::same([$status === 500 ? RuntimeException::class : Nette\Application\BadRequestException::class], $result['errors']);
                Assert::contains('"id":"42"', $result['logs'][0][0]);
                Assert::notContains('private-token', $result['logs'][0][0]);
                if ($status === 500) {
                    Assert::same(['RuntimeException: private-original-failure', Tracy\ILogger::EXCEPTION], $result['logs'][1]);
                }
                if ($late) {
                    Assert::contains('202 Accepted', $headers);
                    Assert::contains('Content-Type: text/plain', $headers);
                    Assert::contains('Cache-Control: private', $headers);
                    Assert::same('already-sent-prefix', $body);
                    Assert::contains('headers already sent', $result['logs'][$status === 500 ? 2 : 1][0]);
                } else {
                    Assert::contains(' ' . $status . ' ', $headers);
                    Assert::contains('Cache-Control: no-store', $headers);
                    if ($ajax) {
                        Assert::contains('Content-Type: application/json', $headers);
                        Assert::same(['error' => true, 'code' => $status], json_decode($body, true));
                    } else {
                        Assert::contains('error ' . $status, $body);
                    }
                }
            }
        }
    }
} finally {
    proc_terminate($process);
    proc_close($process);
    foreach (glob($dir . '/*') as $file) { unlink($file); }
    rmdir($dir);
}
