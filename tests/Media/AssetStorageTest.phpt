<?php
declare(strict_types=1);
use App\Model\AssetStorage;
use App\Model\AssetWrites;
use Nette\Utils\FileSystem;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
$db->query('CREATE TABLE carousel (id INTEGER PRIMARY KEY, title TEXT, image TEXT)');
$db->query('CREATE TABLE links (id INTEGER PRIMARY KEY, title TEXT)');
$db->query('CREATE TABLE menu_menus (id INTEGER PRIMARY KEY)');
$db->query('CREATE TABLE menu (id INTEGER PRIMARY KEY, parent_id INTEGER REFERENCES menu(id) ON DELETE CASCADE, menu_menus_id INTEGER REFERENCES menu_menus(id) ON DELETE CASCADE)');
$logger = new class implements Tracy\ILogger {
    public array $messages = [];
    public function log($value, $priority = self::INFO) { $this->messages[] = (string) $value; }
};
Tracy\Debugger::setLogger($logger);
$root = sys_get_temp_dir() . '/asset-test-' . bin2hex(random_bytes(8));
mkdir($root);
$store = new AssetStorage($db, $root);
$writes = new AssetWrites($db, $root);
$put = static function ($path): void { FileSystem::write($path, 'new'); };
try {
    FileSystem::write($root . '/images/old.png', 'old');
    Assert::exception(function () use ($store, $put): void { $store->run(function ($s) use ($put): void { $s->put('images/old.png', $put); }); }, Nette\Application\BadRequestException::class);
    Assert::same('old', file_get_contents($root . '/images/old.png'));
    Assert::exception(function () use ($store): void { $store->run(function ($s): void { $s->put('images/old.png', static function (): void { throw new RuntimeException('synthetic secret'); }, true); }); }, RuntimeException::class);
    Assert::same('old', file_get_contents($root . '/images/old.png'));
    // A failed second write restores an already replaced first image.
    Assert::exception(function () use ($store, $put): void {
        $store->run(function ($s) use ($put): void {
            $s->put('images/old.png', $put, true);
            $s->put('images/second.png', static function (): void { throw new RuntimeException('move failed'); });
        });
    }, RuntimeException::class);
    Assert::same('old', file_get_contents($root . '/images/old.png'));
    Assert::false(file_exists($root . '/images/second.png'));
    $db->table('carousel')->insert(['id' => 1, 'image' => 'shared.png']);
    $db->table('carousel')->insert(['id' => 2, 'image' => 'shared.png']);
    FileSystem::write($root . '/images/carousel/shared.png', 'shared');
    $writes->deleteCarousel(1);
    Assert::same('shared', file_get_contents($root . '/images/carousel/shared.png'));
    $db->query("CREATE TRIGGER reject_delete BEFORE DELETE ON carousel BEGIN SELECT RAISE(ABORT, 'synthetic secret'); END");
    Assert::exception(function () use ($writes): void { $writes->deleteCarousel(2); }, Nette\Database\DriverException::class);
    Assert::same('shared', file_get_contents($root . '/images/carousel/shared.png'));
    Assert::same(1, $db->table('carousel')->count('*'));
    $db->query('DROP TRIGGER reject_delete');
    $writes->deleteCarousel(2);
    Assert::false(file_exists($root . '/images/carousel/shared.png'));
    // Real failed SQL insertion after a replacement must restore old bytes.
    $db->query("CREATE TRIGGER reject_insert BEFORE INSERT ON links BEGIN SELECT RAISE(ABORT, 'synthetic secret'); END");
    Assert::exception(function () use ($store, $put, $db): void {
        $store->run(function ($s) use ($put, $db): void { $s->put('images/old.png', $put, true); $db->table('links')->insert(['id' => 1]); });
    }, Nette\Database\DriverException::class);
    Assert::same('old', file_get_contents($root . '/images/old.png'));
    foreach (['images/../outside', 'images/menu/../../secret', 'images/old.png/child'] as $path) {
        Assert::exception(function () use ($store, $path): void { $store->run(function ($s) use ($path): void { $s->remove($path); }); }, Nette\Application\BadRequestException::class);
    }
    FileSystem::createDir($root . '/outside');
    symlink($root . '/outside', $root . '/images/linked');
    Assert::exception(function () use ($store): void { $store->run(function ($s): void { $s->remove('images/linked'); }); }, Nette\Application\BadRequestException::class);
    unlink($root . '/images/linked');

    // Rows saved before upload names were sanitized hold raw names. Removal
    // accepts any plain basename; separators, control characters, '.', '..'
    // and dotfiles stay rejected as 400 with nothing touched.
    foreach (['fix legacy.jpg', 'Žluťoučký kůň (1).png', 'a..b.png'] as $legacy) {
        FileSystem::write($root . '/images/carousel/' . $legacy, 'legacy');
        $store->run(function ($s) use ($legacy): void { $s->remove('images/carousel/' . $legacy); });
        Assert::false(file_exists($root . '/images/carousel/' . $legacy));
    }
    FileSystem::write($root . '/images/.htaccess', 'deny');
    foreach (['images/.htaccess', 'images/..', 'images/.', "images/a\0b", 'images/a\\b', "images/a\nb", 'images/'] as $path) {
        $error = Assert::exception(function () use ($store, $path): void { $store->run(function ($s) use ($path): void { $s->remove($path); }); }, Nette\Application\BadRequestException::class);
        Assert::same(400, $error->getHttpCode(), $path);
    }
    Assert::same('deny', file_get_contents($root . '/images/.htaccess'));
    // New names stay strict, and every rejection carries a status and a readable message.
    $error = Assert::exception(function () use ($store, $put): void { $store->run(function ($s) use ($put): void { $s->put('images/new name.png', $put); }); }, Nette\Application\BadRequestException::class);
    Assert::same(400, $error->getHttpCode());
    $error = Assert::exception(function () use ($store, $put): void { $store->run(function ($s) use ($put): void { $s->put('images/old.png', $put); }); }, Nette\Application\BadRequestException::class);
    Assert::same([409, AssetStorage::DUPLICATE], [$error->getHttpCode(), $error->getMessage()]);
    $error = Assert::exception(function (): void { AssetStorage::imageName(['name' => 'fake.png', 'tmp_name' => __FILE__, 'error' => UPLOAD_ERR_OK]); }, Nette\Application\BadRequestException::class);
    Assert::same([400, AssetStorage::INVALID_IMAGE], [$error->getHttpCode(), $error->getMessage()]);

    // Deleting a carousel row with a legacy name removes row and file. A stored
    // name that is not a plain basename keeps its file but still deletes the row.
    $db->table('carousel')->insert(['id' => 3, 'image' => 'fix legacy.jpg']);
    FileSystem::write($root . '/images/carousel/fix legacy.jpg', 'legacy');
    $writes->deleteCarousel(3);
    Assert::false(file_exists($root . '/images/carousel/fix legacy.jpg'));
    Assert::null($db->table('carousel')->get(3));
    $db->table('carousel')->insert(['id' => 4, 'image' => '../outside/keep.png']);
    FileSystem::write($root . '/images/outside/keep.png', 'keep');
    $writes->deleteCarousel(4);
    Assert::null($db->table('carousel')->get(4));
    Assert::same('keep', file_get_contents($root . '/images/outside/keep.png'));
    // A new slide needs an image (carousel.image is NOT NULL): 400, no row.
    $error = Assert::exception(function () use ($writes): void { $writes->carousel(null, ['title' => 'No image'], []); }, Nette\Application\BadRequestException::class);
    Assert::same(400, $error->getHttpCode());
    Assert::same(0, $db->table('carousel')->count('*'));
    $db->table('menu_menus')->insert(['id' => 1]);
    $db->table('menu')->insert(['id' => 1, 'menu_menus_id' => 1]);
    $db->table('menu')->insert(['id' => 2, 'parent_id' => 1]);
    FileSystem::write($root . '/images/menu/1.png', 'parent');
    FileSystem::write($root . '/images/menu/2_h.png', 'child');
    $writes->deleteMenu(1, true);
    Assert::same(0, $db->table('menu')->count('*'));
    Assert::false(file_exists($root . '/images/menu/2_h.png'));
    Assert::notContains('synthetic secret', implode('\n', $logger->messages));
    Assert::same([], glob($root . '/temp/asset-operations/*/restore.json'));
} finally { FileSystem::delete($root); }
