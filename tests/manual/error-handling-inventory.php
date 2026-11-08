<?php
declare(strict_types=1);

// Opt-in diagnostic, no app config/production DB/SMTP. Reports residual gaps;
// intentionally not a regression asserting that the defects must persist.
require __DIR__ . '/../../vendor/autoload.php';
$loader = new Nette\Loaders\RobotLoader();
$loader->addDirectory(__DIR__ . '/../../app');
$loader->setTempDirectory(sys_get_temp_dir() . '/caloriscms-error-inventory-loader');
$loader->register();
require __DIR__ . '/../database.php';

$database = createTestDatabase();
$logger = new class implements Tracy\ILogger {
    public array $entries = [];
    public function log($message, $level = self::INFO) { $this->entries[] = $level; }
};
Tracy\Debugger::setLogger($logger);
$mailer = new class implements Nette\Mail\Mailer {
    public function send(Nette\Mail\Message $mail): void {
        throw new Nette\Mail\SendException('Synthetic mail transport failure');
    }
};
$helpdesk = new class($database, $mailer) extends App\Model\Helpdesk {
    public function getInfo() {
        return (object) ['email' => 'recipient@example.invalid', 'subject' => 'Probe',
            'body' => 'Synthetic message', 'helpdesk_templates_id' => 1, 'log' => 0];
    }
    public function renderBody($subjectContent, $bodyContent, $templateId) { return 'Synthetic message'; }
};
$helpdesk->setId(1);
$helpdesk->setSettings(['contacts:email:hq' => 'sender@example.invalid']);
$helpdesk->setParams([]);
$result = ['helpdesk' => ['sent' => $helpdesk->send(), 'diagnosticLogCount' => count($logger->entries)]];
$logger->entries = [];

$dir = sys_get_temp_dir() . '/caloriscms-error-inventory-' . bin2hex(random_bytes(8));
mkdir($dir, 0700);
define('APP_DIR', $dir);
$context = new Nette\Database\Context($database->getConnection(),
    new Nette\Database\Structure($database->getConnection(), new Nette\Caching\Storages\MemoryStorage()));
try {
    // A file in place of a cache directory reproduces an expected filesystem error.
    // Test the command's catch/report path without touching any real caches.
    mkdir($dir . '/temp/cache', 0700, true);
    file_put_contents($dir . '/temp/cache/latte', 'fixture');
    $cacheCommand = new App\Console\CacheClearCommand($context);
    $tester = new Symfony\Component\Console\Tester\CommandTester($cacheCommand);
    $code = $tester->execute([]);
    $result['cacheClear'] = ['exitCode' => $code,
        'reportsSuccess' => strpos($tester->getDisplay(), 'Cache cleared') !== false,
        'containsOperationContext' => strpos($tester->getDisplay(), 'utilities:cache-clear') !== false,
        'diagnosticLogCount' => count($logger->entries)];
    $logger->entries = [];

    // The in-memory schema deliberately has no pictures table.
    $tester = new Symfony\Component\Console\Tester\CommandTester(new App\Console\ThumbnailGeneratorCommand($context));
    $code = $tester->execute([]);
    $result['thumbnailQueryFailure'] = ['exitCode' => $code,
        'containsOperationContext' => strpos($tester->getDisplay(), 'thumbnail') !== false,
        'diagnosticLogCount' => count($logger->entries)];
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
} finally {
    // Only paths created by this diagnostic; no recursive filesystem removal.
    foreach ([$dir . '/temp/cache/latte'] as $file) {
        if (is_file($file)) { unlink($file); }
    }
    foreach ([$dir . '/temp/cache', $dir . '/temp', $dir] as $folder) {
        if (is_dir($folder)) { rmdir($folder); }
    }
}
