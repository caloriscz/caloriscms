<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) { throw new RuntimeException('Local Docker only.'); }
$mode = $argv[1] ?? '';
$prefix = $argv[2] ?? '';
if (!preg_match('/^permtest-[a-f0-9]{8}$/D', $prefix)) { throw new InvalidArgumentException('Invalid prefix.'); }
$pdo = new PDO('mysql:host=db;dbname=caloriscms;charset=utf8', 'caloris', 'caloris', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$file = sys_get_temp_dir() . '/' . $prefix . '-inline.json';
$user = $prefix . '-pages';
if ($mode === 'create') {
    if (is_file($file)) { throw new RuntimeException('Fixture exists.'); }
    $setting = $pdo->query("SELECT setvalue FROM settings WHERE setkey = 'site:admin:adminBarEnabled'")->fetchColumn();
    file_put_contents($file, json_encode(['setting' => $setting]));
    $pdo->prepare('INSERT INTO snippets (keyword, content) VALUES (?, ?)')->execute([$prefix, 'Original snippet']);
    $snippet = $pdo->lastInsertId();
    $pdo->prepare('INSERT INTO pages (slug,title,document,public,pages_templates_id) VALUES (?, ?, ?, 1, 3)')
        ->execute([$prefix, 'Original title', 'Fixture content']);
    $page = $pdo->lastInsertId();
    $pdo->prepare('UPDATE users SET adminbar_enabled = 1 WHERE username = ?')->execute([$user]);
    $pdo->exec("UPDATE settings SET setvalue = '1' WHERE setkey = 'site:admin:adminBarEnabled'");
    echo json_encode(['page' => $page, 'snippet' => $snippet]);
} elseif ($mode === 'read') {
    $s = $pdo->prepare('SELECT content FROM snippets WHERE keyword = ?'); $s->execute([$prefix]);
    $p = $pdo->prepare('SELECT title FROM pages WHERE slug = ?'); $p->execute([$prefix]);
    echo json_encode(['title' => $p->fetchColumn(), 'snippet' => $s->fetchColumn()]);
} elseif (in_array($mode, ['enable', 'disable', 'missingrole', 'revoke'], true)) {
    $pdo->prepare('UPDATE users SET state = ?, users_roles_id = (SELECT id FROM users_roles WHERE title = ?) WHERE username = ?')
        ->execute([$mode === 'disable' ? 0 : 1, $mode === 'missingrole' ? $prefix . '-absent' : $user, $user]);
    $pdo->prepare('UPDATE users_roles SET pages = ? WHERE title = ?')->execute([$mode === 'revoke' ? 0 : 1, $user]);
} elseif ($mode === 'cleanup') {
    $pdo->prepare('DELETE FROM pages WHERE slug = ?')->execute([$prefix]);
    $pdo->prepare('DELETE FROM snippets WHERE keyword = ?')->execute([$prefix]);
    if (is_file($file)) {
        $state = json_decode(file_get_contents($file), true);
        $pdo->prepare("UPDATE settings SET setvalue = ? WHERE setkey = 'site:admin:adminBarEnabled'")->execute([$state['setting']]);
        unlink($file);
    }
} else { throw new InvalidArgumentException('Invalid mode.'); }
