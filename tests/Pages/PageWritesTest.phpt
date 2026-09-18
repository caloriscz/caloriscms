<?php
declare(strict_types=1);
use App\Model\Document;
use App\Model\PageWrites;
use App\Model\Api\ContentApiService;
use Nette\Utils\ArrayHash;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
$db->query('ALTER TABLE pages ADD slug_en TEXT');
$db->query('ALTER TABLE pages ADD title_en TEXT');
function document($db, string $title): Document {
    $doc = new Document($db);
    $doc->setType(1); $doc->setTemplate(1);
    $doc->setForm(ArrayHash::from(['title' => $title]));
    return $doc;
}
$one = document($db, 'One')->create(1);
$two = document($db, 'Two')->create(1);
$editOne = document($db, 'One');
$editTwo = document($db, 'Two');
// Both request objects have chosen the same proposed slug before either saves.
$editOne->setSlug('one', 'Shared');
$editTwo->setSlug('two', 'Shared');
$editOne->save($one->id, 1);
$editTwo->save($two->id, 1);
Assert::same('shared', $db->table('pages')->get($one->id)->slug);
Assert::same('1-shared', $db->table('pages')->get($two->id)->slug);
// Localized URLs allocate within their own column, not against default slugs.
foreach ([$one, $two] as $row) {
    $doc = document($db, 'English');
    $doc->setLanguage('en');
    $doc->setSlug(null, 'Shared');
    $doc->save($row->id, 1);
}
Assert::same('shared', $db->table('pages')->get($one->id)->slug_en);
Assert::same('1-shared', $db->table('pages')->get($two->id)->slug_en);
Assert::same('shared', $db->table('pages')->get($one->id)->slug);
$api = new ContentApiService($db);
Assert::exception(function () use ($api, $two): void {
    $api->updatePage($two->id, ['slug' => 'shared']);
}, InvalidArgumentException::class, 'Field slug must be unique');
$long = str_repeat('a', 250);
$a = $api->createPage(['title' => $long], 1);
$b = $api->createPage(['title' => $long], 1);
Assert::same(250, strlen($b['slug']));
Assert::notSame($a['slug'], $b['slug']);
Assert::same(0, $a['public']);
Assert::same([3, 5, 7, 9], array_values($db->table('pages')->order('sorted, id')->fetchPairs('id', 'sorted')));
$before = $db->table('pages')->fetchPairs('id', 'sorted');
$db->query("CREATE TRIGGER fail_order BEFORE UPDATE ON pages BEGIN SELECT RAISE(ABORT, 'order failed'); END");
Assert::exception(function () use ($api): void { $api->createPage(['title' => 'Rollback'], 1); }, Nette\Database\DriverException::class);
Assert::same($before, $db->table('pages')->fetchPairs('id', 'sorted'));
$db->query('DROP TRIGGER fail_order');
Assert::exception(function () use ($db): void { PageWrites::uniqueSlug($db, 'x', 'slug_bad;'); }, InvalidArgumentException::class);
