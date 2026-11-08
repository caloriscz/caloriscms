<?php
declare(strict_types=1);

// Temporary tables shadow local tables on this connection; no app config/data.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) { throw new RuntimeException('Local Docker only.'); }
require __DIR__ . '/../bootstrap.php';
$connection = new Nette\Database\Connection('mysql:host=db;dbname=caloriscms;charset=utf8', 'caloris', 'caloris');
$connection->query('CREATE TEMPORARY TABLE menu (id INT AUTO_INCREMENT PRIMARY KEY, title TEXT, title_en TEXT, url TEXT, url_en TEXT, parent_id INT NULL, sorted INT DEFAULT 0)');
$connection->query('CREATE TEMPORARY TABLE languages (id INT PRIMARY KEY, code VARCHAR(8), used INT, `default` INT NULL)');
$connection->query("INSERT INTO languages VALUES (1, 'cs', 1, 1), (2, 'en', 1, NULL)");
$db = new Nette\Database\Explorer($connection, new Nette\Database\Structure($connection, new Nette\Caching\Storages\MemoryStorage()));
$presenter = new class extends Nette\Application\UI\Presenter {
    public function redirect(string $destination, $args = []): void { throw new Nette\Application\AbortException; }
};
$form = new class extends Nette\Forms\BootstrapUIForm {
    public function getHttpData(?int $type = null, ?string $htmlName = null) { return []; }
};
foreach (['parent' => '', 'title' => '<strong>Česky</strong><script>x</script>',
    'title_en' => '<em>English</em><img src="javascript:x" onerror="x">', 'url' => '/cs', 'url_en' => '/en'] as $key => $value) {
    $form->addText($key)->setDefaultValue($value);
}
$control = new App\Forms\Menu\InsertMenuControl($db);
$control->setParent($presenter, 'writer');
try { $control->insertFormSucceeded($form); } catch (Nette\Application\AbortException $expected) { }
$row = $db->table('menu')->fetch();
Tester\Assert::same('<strong>Česky</strong>', $row->title);
Tester\Assert::contains('<em>English</em>', $row->title_en);
Tester\Assert::notContains('javascript:', $row->title_en);
Tester\Assert::notContains('onerror', $row->title_en);
Tester\Assert::same('/cs', $row->url);
Tester\Assert::same('/en', $row->url_en);
Tester\Assert::same(3, $row->sorted);
echo "PASS: actual menu insert callback, default/localized HTML and MariaDB ordering\n";
