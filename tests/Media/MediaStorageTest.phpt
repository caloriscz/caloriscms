<?php
declare(strict_types=1);
use App\Model\MediaStorage;
use App\Model\MediaAudit;
use Nette\Utils\FileSystem;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
foreach (['media', 'pictures'] as $table) {
    $db->query('CREATE TABLE ' . $table . ' (id INTEGER PRIMARY KEY AUTOINCREMENT, pages_id INTEGER, name TEXT,
        filesize INTEGER, file_type INTEGER, date_created TEXT, description TEXT, sorted INTEGER)');
}
$root = sys_get_temp_dir() . '/media-test-' . bin2hex(random_bytes(8));
FileSystem::createDir($root);
define('APP_DIR', $root);
$store = new MediaStorage($db, $root);
$write = static function ($path): void { FileSystem::write($path, 'original'); };
$thumb = static function ($from, $to): void { FileSystem::write($to, 'thumbnail'); };
try {
    $db->table('pages')->insert(['id' => 1, 'title' => 'Test', 'slug' => 'test']);
    Assert::exception(function () use ($store, $write): void {
        $store->store('pictures', 1, 'failed.jpg', $write, static function (): void { throw new RuntimeException('thumbnail failed'); });
    }, RuntimeException::class, 'thumbnail failed');
    Assert::same(0, $db->table('pictures')->count('*'));
    Assert::false(file_exists($root . '/pictures/1/failed.jpg'));
    Assert::exception(function () use ($store): void {
        $store->store('media', 1, 'failed.txt', static function (): void { throw new RuntimeException('move failed'); });
    }, RuntimeException::class, 'move failed');
    $id = $store->store('pictures', 1, 'saved.jpg', $write, $thumb);
    Assert::same('original', file_get_contents($root . '/pictures/1/saved.jpg'));
    Assert::same('thumbnail', file_get_contents($root . '/pictures/1/tn/saved.jpg'));
    Assert::exception(function () use ($store, $write): void {
        $store->store('pictures', 1, 'saved.jpg', $write);
    }, Nette\Application\BadRequestException::class);
    Assert::same('original', file_get_contents($root . '/pictures/1/saved.jpg'));
    // A real SQL error after file placement must remove the new files.
    $db->query("CREATE TRIGGER reject_insert BEFORE INSERT ON media BEGIN SELECT RAISE(ABORT, 'reject'); END");
    Assert::exception(function () use ($store, $write): void { $store->store('media', 1, 'db-failed.txt', $write); }, Nette\Database\DriverException::class);
    Assert::false(file_exists($root . '/media/1/db-failed.txt'));
    $db->query('DROP TRIGGER reject_insert');
    // A real SQL error after staging deletion must restore both files and rows.
    $db->query("CREATE TRIGGER reject_delete BEFORE DELETE ON pictures BEGIN SELECT RAISE(ABORT, 'reject'); END");
    Assert::exception(function () use ($store, $id): void { $store->deleteFile('pictures', $id); }, Nette\Database\DriverException::class);
    Assert::same('original', file_get_contents($root . '/pictures/1/saved.jpg'));
    Assert::same('thumbnail', file_get_contents($root . '/pictures/1/tn/saved.jpg'));
    Assert::same(1, $db->table('pictures')->count('*'));
    $db->query('DROP TRIGGER reject_delete');
    FileSystem::delete($root . '/pictures/1/saved.jpg');
    FileSystem::write($root . '/media/1/unreferenced.txt', 'orphan');
    $db->table('media')->insert(['pages_id' => 999, 'name' => 'missing.txt']);
    $report = MediaAudit::inspect($db, $root);
    Assert::count(2, $report['missing_originals']);
    Assert::count(1, $report['orphan_rows']);
    Assert::same(['media/1/unreferenced.txt'], $report['unreferenced_files']);
    Assert::same([], $report['pending_operations']);
    Assert::same(1, $store->deleteFile('pictures', $id));
    Assert::false(file_exists($root . '/pictures/1/tn/saved.jpg'));
    Assert::null($store->deleteFile('pictures', $id));
    // Page delete covers originals, thumbnails and picture rows without a FK.
    $store->store('pictures', 1, 'page.jpg', $write, $thumb);
    $store->store('media', 1, 'page.txt', $write);
    $db->query("CREATE TRIGGER reject_page BEFORE DELETE ON pages BEGIN SELECT RAISE(ABORT, 'reject'); END");
    Assert::exception(function () use ($store): void { $store->deletePage(1); }, Nette\Database\DriverException::class);
    Assert::true(is_file($root . '/media/1/page.txt'));
    Assert::true(is_file($root . '/pictures/1/tn/page.jpg'));
    Assert::same(1, $db->table('pictures')->count('*'));
    $db->query('DROP TRIGGER reject_page');
    Assert::true((new App\Model\Document($db))->delete(1));
    Assert::false(file_exists($root . '/media/1'));
    Assert::false(file_exists($root . '/pictures/1'));
    Assert::same(0, $db->table('pictures')->count('*'));
    Assert::same(0, $db->table('media')->where('pages_id', 1)->count('*'));
    Assert::false($store->deletePage(1));
    Assert::exception(function () use ($store, $write): void { $store->store('media', 999, 'missing.txt', $write); }, Nette\Application\BadRequestException::class);
    Assert::same([], MediaAudit::inspect($db, $root)['pending_operations']);
} finally {
    FileSystem::delete($root);
}
