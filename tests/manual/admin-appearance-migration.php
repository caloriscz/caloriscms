<?php
declare(strict_types=1);

// Connection-local TEMPORARY table shadows users_roles; real role data is untouched.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) {
    throw new RuntimeException('Run only in the local Docker app container.');
}
require __DIR__ . '/../../vendor/autoload.php';
use Tester\Assert;

$pdo = new PDO('mysql:host=db;dbname=caloriscms;charset=utf8', 'caloris', 'caloris',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$sql = file_get_contents(__DIR__ . '/../../app/model/migrations/2026_09_23_admin_appearance.sql');
$pdo->exec('CREATE TEMPORARY TABLE users_roles (id INT PRIMARY KEY, title VARCHAR(40) COLLATE utf8_general_ci, appearance TINYINT, pages TINYINT) ENGINE=InnoDB');
try {
    $pdo->exec("INSERT INTO users_roles VALUES (1, 'Admin', 0, 1), (2, 'Editor', 0, 1), (3, 'Admin', 0, 0)");
    Assert::same(1, $pdo->exec($sql));
    Assert::same(0, $pdo->exec($sql));
    Assert::same('1:Admin:1:1,2:Editor:0:1,3:Admin:0:0', $pdo->query(
        "SELECT GROUP_CONCAT(CONCAT(id, ':', title, ':', appearance, ':', pages) ORDER BY id) FROM users_roles")->fetchColumn());
    foreach (['Custom role', 'admin'] as $title) {
        $pdo->prepare('UPDATE users_roles SET title = ?, appearance = 0 WHERE id = 1')->execute([$title]);
        Assert::same(0, $pdo->exec($sql));
        Assert::same(0, (int) $pdo->query('SELECT appearance FROM users_roles WHERE id = 1')->fetchColumn());
    }
    $pdo->exec('DELETE FROM users_roles WHERE id = 1');
    Assert::same(0, $pdo->exec($sql));
    echo "PASS: intended Admin only; idempotent; other, renamed and absent roles unchanged\n";
} finally {
    $pdo->exec('DROP TEMPORARY TABLE users_roles');
}
