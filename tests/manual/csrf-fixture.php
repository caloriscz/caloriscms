<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) { throw new RuntimeException('Local Docker only.'); }
$mode = $argv[1] ?? '';
$prefix = $argv[2] ?? '';
if (!preg_match('/^permtest-[a-f0-9]{8}$/D', $prefix)) { throw new InvalidArgumentException('Invalid prefix.'); }
$pdo = new PDO('mysql:host=db;dbname=caloriscms;charset=utf8', 'caloris', 'caloris', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$file = sys_get_temp_dir() . '/' . $prefix . '-csrf.json';
$records = [
    'menu_menus' => ['title' => $prefix],
    'menu' => ['title' => $prefix],
    'contacts_categories' => ['title' => $prefix],
    'contacts' => ['name' => $prefix, 'users_id' => null, 'countries_id' => null, 'order' => 0],
    'links_categories' => ['title' => $prefix],
    'links' => ['title' => $prefix],
    'snippets' => ['keyword' => $prefix, 'content' => $prefix],
    'helpdesk' => ['title' => $prefix, 'subject' => $prefix, 'helpdesk_templates_id' => 1, 'log' => 0],
    'helpdesk_messages' => ['message' => $prefix, 'email' => $prefix . '@example.invalid', 'ipaddress' => '127.0.0.1'],
    'carousel' => ['title' => $prefix, 'uri' => '', 'image' => $prefix . '.missing', 'visible' => 0, 'sorted' => 0],
];
if ($mode === 'create') {
    if (is_file($file)) { throw new RuntimeException('Fixture exists.'); }
    $pdo->beginTransaction();
    try {
        $ids = [];
        foreach ($records as $table => $values) {
            if ($table === 'menu') { $values['menu_menus_id'] = $ids['menu_menus']; }
            if ($table === 'contacts') { $values['contacts_categories_id'] = $ids['contacts_categories']; }
            if ($table === 'links') { $values['links_categories_id'] = $ids['links_categories']; }
            if ($table === 'helpdesk_messages') { $values['helpdesk_id'] = $ids['helpdesk']; }
            $columns = '`' . implode('`, `', array_keys($values)) . '`';
            $params = implode(',', array_fill(0, count($values), '?'));
            $pdo->prepare("INSERT INTO `$table` ($columns) VALUES ($params)")->execute(array_values($values));
            $ids[$table] = (int) $pdo->lastInsertId();
        }
        file_put_contents($file, json_encode($ids));
        $pdo->commit();
        echo json_encode($ids);
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
} elseif ($mode === 'snapshot') {
    $hashes = [];
    foreach (array_merge(array_keys($records), ['contacts_openinghours', 'settings', 'pages', 'media', 'pictures']) as $table) {
        $rows = $pdo->query("SELECT * FROM `$table` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $hashes[$table] = hash('sha256', json_encode($rows));
    }
    $users = $pdo->query('SELECT id, username, email, name, password, state, users_roles_id, adminbar_enabled FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $hashes['users'] = hash('sha256', json_encode($users));
    echo json_encode($hashes);
} elseif ($mode === 'read') {
    $ids = json_decode(file_get_contents($file), true);
    $rows = [];
    foreach ($ids as $table => $id) {
        $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?"); $stmt->execute([$id]);
        $rows[$table] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Only disposable records are returned; no real content or credentials.
    echo json_encode($rows);
} elseif ($mode === 'cleanup') {
    if (is_file($file)) {
        $ids = json_decode(file_get_contents($file), true);
        foreach (array_reverse($ids, true) as $table => $id) {
            if (!isset($records[$table])) { throw new RuntimeException('Unexpected fixture table.'); }
            $pdo->prepare("DELETE FROM `$table` WHERE id = ?")->execute([$id]);
        }
        unlink($file);
    }
} else { throw new InvalidArgumentException('Invalid mode.'); }
