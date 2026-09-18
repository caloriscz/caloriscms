<?php
declare(strict_types=1);

// Opt-in disposable local records, isolated from application configuration.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) {
    throw new RuntimeException('Run only in the local Docker app container.');
}
$mode = $argv[1] ?? '';
$prefix = $argv[2] ?? '';
if (!preg_match('/^permtest-[a-f0-9]{8}$/D', $prefix)
    || !in_array($mode, ['create', 'unchanged', 'deleted', 'cleanup'], true)) {
    throw new InvalidArgumentException('Expected create|unchanged|deleted|cleanup and permtest-xxxxxxxx.');
}
$pdo = new PDO('mysql:host=db;dbname=caloriscms;charset=utf8', 'caloris', 'caloris', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$records = [
    'menu' => ['title' => $prefix],
    'contacts' => ['name' => $prefix, 'users_id' => null, 'countries_id' => null, 'order' => 0],
    'links' => ['title' => $prefix],
    'snippets' => ['keyword' => $prefix, 'content' => $prefix],
    'helpdesk_messages' => ['message' => $prefix, 'email' => $prefix . '@example.invalid', 'ipaddress' => '127.0.0.1'],
    'carousel' => ['title' => $prefix, 'uri' => '', 'image' => $prefix . '.missing', 'visible' => 0, 'sorted' => 0],
];
$ids = [];
$pdo->beginTransaction();
try {
    foreach ($records as $table => $values) {
        $key = array_keys($values)[0];
        $select = $pdo->prepare("SELECT * FROM `$table` WHERE `$key` = ?");
        $select->execute([$prefix]);
        $rows = $select->fetchAll(PDO::FETCH_ASSOC);
        if ($mode === 'create') {
            if ($rows) {
                throw new RuntimeException('Fixture already exists.');
            }
            $columns = '`' . implode('`, `', array_keys($values)) . '`';
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $pdo->prepare("INSERT INTO `$table` ($columns) VALUES ($placeholders)")->execute(array_values($values));
            $ids[] = $pdo->lastInsertId();
        } elseif ($mode === 'unchanged') {
            if (count($rows) !== 1) {
                throw new RuntimeException('Denied request changed fixture in ' . $table);
            }
            foreach ($values as $column => $value) {
                if ((string) $rows[0][$column] !== (string) $value) {
                    throw new RuntimeException('Denied request changed ' . $table . '.' . $column);
                }
            }
        } elseif ($mode === 'deleted' && $rows) {
            throw new RuntimeException('Permitted deletion failed in ' . $table);
        } elseif ($mode === 'cleanup') {
            $pdo->prepare("DELETE FROM `$table` WHERE `$key` = ?")->execute([$prefix]);
        }
    }
    $pdo->commit();
    echo $mode === 'create' ? implode(',', $ids) . "\n" : $mode . " OK\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
