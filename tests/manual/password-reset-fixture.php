<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) {
    throw new RuntimeException('Local Docker only.');
}
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/Security/PasswordReset.php';
$mode = $argv[1] ?? '';
$name = $argv[2] ?? '';
if (!in_array($mode, ['migrate', 'create', 'check', 'cleanup'], true)
    || !preg_match('/^resettest-[a-f0-9]{8}$/D', $name)) {
    throw new InvalidArgumentException('Expected mode and resettest-xxxxxxxx.');
}
$connection = new Nette\Database\Connection('mysql:host=db;dbname=caloriscms;charset=utf8', 'caloris', 'caloris');
$db = new Nette\Database\Explorer($connection, new Nette\Database\Structure($connection,
    new Nette\Caching\Storages\MemoryStorage()));
if ($mode === 'migrate') {
    $sql = file_get_contents(__DIR__ . '/../../app/model/migrations/2026_09_16_password_reset.sql');
    for ($i = 0; $i < 2; $i++) {
        $connection->getPdo()->exec($sql);
    }
    echo "Migration applied twice successfully to local Docker database.\n";
} elseif ($mode === 'create') {
    if ($db->table('users')->where('username', $name)->count()) { throw new RuntimeException('Fixture exists'); }
    $user = $db->table('users')->insert(['username' => $name, 'email' => $name . '@example.invalid',
        'password' => password_hash('BeforeReset1!', PASSWORD_BCRYPT), 'state' => 1, 'users_roles_id' => 1]);
    $token = (new App\Security\PasswordReset($db))->issue((int) $user->id);
    echo json_encode(['id' => $user->id, 'token' => $token]) . "\n";
} elseif ($mode === 'check') {
    $user = $db->table('users')->where('username', $name)->fetch();
    echo json_encode(['original_password' => password_verify('BeforeReset1!', $user->password),
        'new_password' => password_verify('AfterReset1!', $user->password),
        'token_present' => $user->reset_token_hash !== null]) . "\n";
} else {
    $db->table('users')->where(['username' => $name, 'email' => $name . '@example.invalid'])->delete();
    echo "Fixture removed.\n";
}
