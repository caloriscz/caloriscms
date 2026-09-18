<?php
declare(strict_types=1);

// Opt-in local Docker smoke fixture. Never loads application configuration.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) {
    throw new RuntimeException('Run only in the local Docker app container.');
}
$mode = $argv[1] ?? '';
$prefix = $argv[2] ?? '';
if (!preg_match('/^permtest-[a-f0-9]{8}$/D', $prefix) || !in_array($mode, ['create', 'check', 'cleanup'], true)) {
    throw new InvalidArgumentException('Expected create|check|cleanup and a unique permtest-xxxxxxxx prefix.');
}
$pdo = new PDO('mysql:host=db;dbname=caloriscms;charset=utf8', 'caloris', 'caloris', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$permissions = ['sign', 'pages', 'pictures', 'media', 'members', 'settings', 'menu', 'contacts', 'helpdesk', 'appearance'];
$roles = ['denied' => ['sign'], 'full' => $permissions];
foreach (['pages', 'media', 'menu', 'contacts', 'helpdesk', 'appearance'] as $permission) {
    $roles[$permission] = ['sign', $permission];
}
$pdo->beginTransaction();
try {
    foreach ($roles as $name => $grants) {
        $flags = array_map(static function (string $permission) use ($grants): int {
            return in_array($permission, $grants, true) ? 1 : 0;
        }, $permissions);
        $username = $prefix . '-' . $name;
        if ($mode === 'create') {
            $exists = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
            $exists->execute([$username]);
            if ((int) $exists->fetchColumn() !== 0) {
                throw new RuntimeException('Fixture already exists.');
            }
            $stmt = $pdo->prepare('INSERT INTO users_roles (title, sign, pages, pictures, media, members, settings, menu, contacts, helpdesk, appearance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute(array_merge([$username], $flags));
            $roleId = (int) $pdo->lastInsertId();
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, state, users_roles_id, adminbar_enabled) VALUES (?, ?, ?, 1, ?, 0)');
            $stmt->execute([$username, $username . '@example.invalid', password_hash('LocalPermissionSmoke1!', PASSWORD_BCRYPT), $roleId]);
        } elseif ($mode === 'check') {
            $stmt = $pdo->prepare('SELECT password FROM users WHERE username = ?');
            $stmt->execute([$username]);
            if (!password_verify('LocalPermissionSmoke1!', (string) $stmt->fetchColumn())) {
                throw new RuntimeException('Fixture account was deleted or its password changed.');
            }
        } else {
            $stmt = $pdo->prepare('DELETE FROM users WHERE username = ? AND email = ?');
            $stmt->execute([$username, $username . '@example.invalid']);
            $stmt = $pdo->prepare('DELETE FROM users_roles WHERE title = ?');
            $stmt->execute([$username]);
        }
    }
    $pdo->commit();
    echo $mode . " OK\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
