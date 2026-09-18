<?php
declare(strict_types=1);

use App\Security\InlineEditing;
use Nette\Application\BadRequestException;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
$db->query('CREATE TABLE snippets (id INTEGER PRIMARY KEY, content TEXT, content_en TEXT)');
$db->query('CREATE TABLE languages (id INTEGER PRIMARY KEY, code TEXT, used INTEGER)');
$db->query("INSERT INTO languages VALUES (1, 'en', 1)");
$db->query('ALTER TABLE pages ADD COLUMN title_en TEXT');
$db->table('pages')->insert(['id' => 10, 'slug' => 'test', 'title' => 'Original', 'editable' => 1]);
$db->table('snippets')->insert(['id' => 10, 'content' => 'Original']);
Assert::true(InlineEditing::allows(['state' => '1'], ['sign' => '1', 'pages' => '1']));
foreach ([[[], []], [['state' => 0], ['sign' => 1, 'pages' => 1]],
    [['state' => 1], ['sign' => 0, 'pages' => 1]], [['state' => 1], ['sign' => 1]]] as [$member, $role]) {
    Assert::false(InlineEditing::allows($member, $role));
}
$save = static function ($kind, $id, $text, $locale = 'cs') use ($db): string {
    return InlineEditing::save($db, $kind, $id, $text, $locale, 'cs');
};
foreach ([['title', [], 'text'], ['title', '0', 'text'], ['title', '10', []],
    ['title', '10', '<script>x</script>'], ['title', '10', str_repeat('a', 251)],
    ['title', '10', ''], ['snippet', '10', str_repeat('a', 60001)]] as $args) {
    Assert::exception(static function () use ($save, $args): void { $save(...$args); }, BadRequestException::class);
}
Assert::same('Original', $db->table('pages')->get(10)->title);
Assert::same('Original', $db->table('snippets')->get(10)->content);
$title = 'Žluťoučký & + "quoted" = 100%';
Assert::same($title, $save('title', '10', $title));
Assert::same($title, $db->table('pages')->get(10)->title);
Assert::same('English', $save('title', '10', 'English', 'en'));
Assert::same($title, $db->table('pages')->get(10)->title);
Assert::same('English', $db->table('pages')->get(10)->title_en);
$clean = $save('snippet', '10', '<strong>Safe &amp; sound</strong><script>alert(1)</script><img src="x" onerror="alert(1)">');
Assert::contains('<strong>Safe &amp; sound</strong>', $clean);
Assert::notContains('<script', $clean);
Assert::notContains('onerror', $clean);
Assert::exception(static function () use ($save): void { $save('title', '9999', 'Absent'); }, BadRequestException::class);
Assert::exception(static function () use ($save): void { $save('title', '10', 'No', 'xx'); }, BadRequestException::class);
$db->table('pages')->where('id', 10)->update(['editable' => 0]);
Assert::exception(static function () use ($save): void { $save('title', '10', 'Locked'); }, Nette\Application\ForbiddenRequestException::class);
Assert::same($title, $db->table('pages')->get(10)->title);
