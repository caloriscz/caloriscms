<?php
declare(strict_types=1);
// Opt-in local Docker-only MariaDB test; all writes use a newly created schema.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) { throw new RuntimeException('Local Docker only.'); }
require __DIR__ . '/../../vendor/autoload.php';
$loader = new Nette\Loaders\RobotLoader;
$loader->addDirectory(__DIR__ . '/../../app')->setTempDirectory('/tmp/cms-concurrency-loader')->register();
function connection(string $schema): Nette\Database\Explorer {
    if (!preg_match('/^cms_race_[a-f0-9]{12}$/D', $schema)) { throw new RuntimeException('Unsafe test schema.'); }
    $c = new Nette\Database\Connection('mysql:host=db;dbname=' . $schema, 'root', 'caloris_root');
    return new Nette\Database\Explorer($c, new Nette\Database\Structure($c, new Nette\Caching\Storages\MemoryStorage()));
}
function check(bool $condition, string $message): void { if (!$condition) { throw new RuntimeException($message); } }
if (($argv[1] ?? '') === 'worker') {
    [$script, $mode, $schema, $gate, $kind, $index] = $argv;
    $db = connection($schema);
    $db->getConnection()->onQuery[] = static function ($connection, $result): void {
        if ($result instanceof Nette\Database\ResultSet && stripos($result->getQueryString(), 'COUNT') !== false
            && stripos($result->getQueryString(), 'slug') !== false) { usleep(200000); }
    };
    file_put_contents($gate . '/ready-' . $index, 'ready');
    $deadline = microtime(true) + 10;
    while (!is_file($gate . '/go')) {
        if (microtime(true) > $deadline) { throw new RuntimeException('Start gate timeout.'); }
        usleep(10000);
    }
    if ($kind === 'admin') {
        $doc = new App\Model\Document($db);
        $doc->setForm(Nette\Utils\ArrayHash::from(['title' => 'Concurrent page']));
        $doc->setType(1);
        $doc->setTemplate(1);
        $doc->create(1);
    } else {
        (new App\Model\Api\ContentApiService($db))->createPage(['title' => 'Concurrent page'], 1);
    }
    exit(0);
}
$root = new PDO('mysql:host=db', 'root', 'caloris_root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$schema = 'cms_race_' . bin2hex(random_bytes(6));
$gate = '/tmp/' . $schema;
$processes = [];
$root->exec('CREATE DATABASE `' . $schema . '` CHARACTER SET utf8 COLLATE utf8_czech_ci');
try {
    mkdir($gate);
    $db = connection($schema);
    $db->query('CREATE TABLE pages LIKE caloriscms.pages');
    $db->query('CREATE TABLE users (id INT PRIMARY KEY, state INT)');
    $db->query('INSERT INTO users VALUES (1,1)');
    $db->query('CREATE TABLE pages_types (id INT PRIMARY KEY, pages_templates_id INT)');
    $db->query('INSERT INTO pages_types VALUES (1,1)');
    $db->query('CREATE TABLE pages_templates (id INT PRIMARY KEY)');
    $db->query('INSERT INTO pages_templates VALUES (1)');
    for ($i = 0; $i < 4; $i++) {
        $command = [PHP_BINARY, __FILE__, 'worker', $schema, $gate, $i % 2 ? 'api' : 'admin', (string) $i];
        $processes[] = proc_open($command, [0 => ['file', '/dev/null', 'r'], 1 => ['file', $gate . '/out-' . $i, 'w'], 2 => ['file', $gate . '/err-' . $i, 'w']], $pipes);
    }
    $deadline = microtime(true) + 15;
    while (count(glob($gate . '/ready-*')) !== 4) {
        if (microtime(true) > $deadline) { throw new RuntimeException('Worker readiness timeout.'); }
        usleep(10000);
    }
    file_put_contents($gate . '/go', 'go');
    foreach ($processes as $i => $process) {
        check(proc_close($process) === 0, 'Worker failed: ' . file_get_contents($gate . '/err-' . $i) . file_get_contents($gate . '/out-' . $i));
    }
    $processes = [];
    $rows = $db->table('pages')->fetchAll();
    $slugs = array_column(array_map(static function ($row) { return $row->toArray(); }, $rows), 'slug');
    sort($slugs);
    echo 'Concurrent slugs: ' . json_encode($slugs) . "\n";
    if (($argv[1] ?? '') === 'reproduce') {
        check(count(array_unique($slugs)) < 4, 'Race did not reproduce.');
        echo "REPRODUCED: competing real admin/API creates produced duplicate slugs.\n";
    } else {
        check($slugs === ['1-concurrent-page', '2-concurrent-page', '3-concurrent-page', 'concurrent-page'], 'Concurrent slugs were not unique/deterministic.');
        $orders = array_values($db->table('pages')->order('sorted, id')->fetchPairs('id', 'sorted'));
        check($orders === [3, 5, 7, 9], 'Ordering not stable: ' . json_encode($orders));
        check($db->table('pages')->where('public', 0)->count('*') === 4, 'Creation published a page.');
        // Fail the ordering update after insert: the complete create must roll back.
        $db->query("CREATE TRIGGER reject_order BEFORE UPDATE ON pages FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test order failure'");
        $before = $db->table('pages')->fetchPairs('id', 'sorted');
        foreach (['admin', 'api'] as $kind) {
            $failed = false;
            try {
                if ($kind === 'admin') {
                    $doc = new App\Model\Document($db);
                    $doc->setForm(Nette\Utils\ArrayHash::from(['title' => 'Must roll back']));
                    $doc->setType(1); $doc->setTemplate(1); $doc->create(1);
                } else { (new App\Model\Api\ContentApiService($db))->createPage(['title' => 'Must roll back'], 1); }
            } catch (Nette\Database\DriverException $error) { $failed = true; }
            check($failed && $db->table('pages')->fetchPairs('id', 'sorted') === $before, 'Failed create left partial data.');
            check((int) $db->query("SELECT IS_FREE_LOCK(CONCAT('caloris:pages:', SHA1(DATABASE())))")->fetchField() === 1,
                'Failed create leaked its named lock.');
        }
        $db->query('DROP TRIGGER reject_order');
        // A subsequent successful create remains possible after rollback.
        (new App\Model\Api\ContentApiService($db))->createPage(['title' => 'Retry works'], 1);
        echo "PASS: mixed admin/API concurrency, draft state, deterministic order, SQL rollback and retry.\n";
    }
} finally {
    foreach ($processes as $process) { if (is_resource($process)) { proc_terminate($process); proc_close($process); } }
    $root->exec('DROP DATABASE `' . $schema . '`');
    Nette\Utils\FileSystem::delete($gate);
}
