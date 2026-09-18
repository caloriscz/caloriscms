<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv') || !preg_match('/^permtest-[a-f0-9]{8}$/D', $argv[1] ?? '')) {
    throw new RuntimeException('Local Docker fixture prefix required.');
}
$name = $argv[1] . '-created';
$pdo = new PDO('mysql:host=db;dbname=caloriscms;charset=utf8', 'caloris', 'caloris', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$stmt = $pdo->prepare('SELECT password, users_roles_id, reset_token_hash FROM users WHERE username = ? AND email = ?');
$stmt->execute([$name, $name . '@example.invalid']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row || $row['users_roles_id'] !== null || $row['reset_token_hash'] !== null
    || password_get_info($row['password'])['algoName'] !== 'bcrypt') {
    throw new RuntimeException('Unexpected created-member fixture state.');
}
$stmt = $pdo->prepare('DELETE FROM users WHERE username = ? AND email = ?');
$stmt->execute([$name, $name . '@example.invalid']);
echo "Created member had a hashed unknown password, no assigned role and no token/mail; fixture removed.\n";
