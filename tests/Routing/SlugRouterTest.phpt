<?php
declare(strict_types=1);

use App\SlugRouter;
use Model\SlugManager;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';

class RouterTestTranslator extends Symfony\Component\Translation\Translator
{
    public function getAvailableLocales(): array
    {
        return ['cs', 'en'];
    }
}

$db = createTestDatabase();
foreach ([
    'ALTER TABLE pages_types ADD presenter TEXT',
    'ALTER TABLE pages_types ADD action TEXT',
    'ALTER TABLE pages_types ADD prefix TEXT',
    'ALTER TABLE pages_types ADD pages_templates_id INTEGER REFERENCES pages_templates(id)',
    'ALTER TABLE pages_templates ADD template TEXT',
    'ALTER TABLE pages ADD slug_en TEXT',
] as $sql) {
    $db->query($sql);
}
$db->table('pages_types')->get(1)->update(['presenter' => 'Front:Pages', 'action' => 'default', 'prefix' => 'news']);
$db->table('pages')->insert(['id' => 10, 'slug' => 'article', 'slug_en' => 'english-article',
    'title' => 'Article', 'public' => 1, 'pages_types_id' => 1]);
$router = new SlugRouter(new SlugManager($db, new RouterTestTranslator('cs')));

$match = $router->match(new Request(new UrlScript('http://example.test/article?q=a%26b%3Dc%2Bd+e&empty=&bare&tag=first&tag=last&filters[]=one&filters[]=two', '/')));
Assert::same('a&b=c+d e', $match['q']);
Assert::same('', $match['empty']);
Assert::same('', $match['bare']);
Assert::same('last', $match['tag']);
Assert::same(['one', 'two'], $match['filters']);
Assert::false(isset($match[0]));

// CMS route identity comes from the path/database, never from user query input.
$match = $router->match(new Request(new UrlScript('http://example.test/en/news/english-article?presenter=Admin:Members&module=Admin&action=delete&page_id=999&slug=bad&locale=xx&prefix=bad&method=DELETE&do=pagetitle', '/')));
Assert::same('Front:Pages', $match['presenter']);
Assert::same('default', $match['action']);
Assert::same(10, $match['page_id']);
Assert::same('en', $match['locale']);
Assert::same('news', $match['prefix']);
Assert::same('GET', $match['method']);
Assert::false(isset($match['do']));
Assert::false(isset($match['slug']));
Assert::false(isset($match['module']));

$ref = new UrlScript('http://example.test:8090/cms/current', '/cms/index.php');
$url = $router->constructUrl([
    'presenter' => 'Front:Pages', 'action' => 'default', 'page_id' => 10,
    'locale' => 'en', 'prefix' => 'news', 'q' => 'a&b=c+d e',
    'empty' => '', 'filters' => ['one', 'two'],
], $ref);
Assert::same('http://example.test:8090/cms/en/news/english-article?q=a%26b%3Dc%2Bd%20e&empty=&filters%5B0%5D=one&filters%5B1%5D=two', $url);
$match = $router->match(new Request(new UrlScript($url, '/cms/index.php')));
Assert::same(10, $match['page_id']);
Assert::same('a&b=c+d e', $match['q']);
Assert::same(['one', 'two'], $match['filters']);

foreach ([
    ['slug' => 'article'],
    ['slug' => 'article', 'prefix' => 'news'],
    ['page_id' => 10, 'locale' => 'en', 'prefix' => 'news'],
] as $route) {
    $url = $router->constructUrl($route + ['action' => 'default', 'id' => 42], new UrlScript('http://example.test/'));
    Assert::notContains('//42', $url);
    $match = $router->match(new Request(new UrlScript($url . '?id=999', '/')));
    Assert::same(10, $match['page_id']);
    Assert::same('42', $match['id']);
}
Assert::same('http://example.test/article/42', $router->constructUrl(['slug' => 'article', 'id' => 42], new UrlScript('http://example.test/')));
Assert::null($router->match(new Request(new UrlScript('http://example.test/no-such-page?bare', '/'))));
Assert::null($router->constructUrl(['page_id' => 999], $ref));

// Default-language links use the unsuffixed column, and non-default actions keep
// their legacy trailing slash without duplicating it before an id.
Assert::same('http://example.test/cs/article', $router->constructUrl(['page_id' => 10, 'locale' => 'cs'], new UrlScript('http://example.test/')));
Assert::same('http://example.test/article/', $router->constructUrl(['slug' => 'article', 'action' => 'detail'], new UrlScript('http://example.test/')));
Assert::same('http://example.test/article/42', $router->constructUrl(['slug' => 'article', 'action' => 'detail', 'id' => 42], new UrlScript('http://example.test/')));

$db->table('pages_templates')->get(1)->update(['template' => 'Front:Media:folderList']);
$db->table('pages')->get(10)->update(['pages_templates_id' => 1]);
$match = $router->match(new Request(new UrlScript('http://example.test/article?page=2', '/')));
Assert::same('Front:Media', $match['presenter']);
Assert::same('folderList', $match['action']);
Assert::same('2', $match['page']);
$db->table('pages_templates')->get(1)->update(['template' => 'invalid']);
Assert::null($router->match(new Request(new UrlScript('http://example.test/article', '/'))));

// Older installations lack the foreign key from pages.pages_types_id. Without it a
// 'pages_types.prefix' join throws "No reference found for $pages->pages_types", and every
// two- or three-part URL (e.g. /pages/search/) was a 500 before reaching its route.
$connection = new Nette\Database\Connection('sqlite::memory:');
$connection->getPdo()->exec('
    CREATE TABLE pages_templates (id INTEGER PRIMARY KEY, template TEXT);
    CREATE TABLE pages_types (id INTEGER PRIMARY KEY, presenter TEXT, action TEXT, prefix TEXT,
        pages_templates_id INTEGER);
    CREATE TABLE pages (id INTEGER PRIMARY KEY, slug TEXT, slug_en TEXT, title TEXT, public INTEGER,
        pages_types_id INTEGER, pages_templates_id INTEGER);
    INSERT INTO pages_types (id, presenter, action, prefix) VALUES (1, \'Front:Pages\', \'default\', \'news\'),
        (2, \'Front:Pages\', \'default\', NULL);
    INSERT INTO pages (id, slug, title, public, pages_types_id) VALUES (10, \'article\', \'Article\', 1, 1),
        (11, \'other\', \'Other\', 1, 2), (12, \'hidden\', \'Hidden\', 0, 1);
');
// Discovered conventions, as the application's container uses: they resolve joins only through
// real foreign keys. (createTestDatabase() uses static conventions, which never need a key.)
$structure = new Nette\Database\Structure($connection, new Nette\Caching\Storages\MemoryStorage());
$noKeys = new Nette\Database\Explorer($connection, $structure,
    new Nette\Database\Conventions\DiscoveredConventions($structure));
$router = new SlugRouter(new SlugManager($noKeys, new RouterTestTranslator('cs')));
$match = $router->match(new Request(new UrlScript('http://example.test/news/article', '/')));
Assert::same(10, $match['page_id']);
Assert::same('news', $match['prefix']);
$match = $router->match(new Request(new UrlScript('http://example.test/news/article/42', '/')));
Assert::same(10, $match['page_id']);
Assert::same('42', $match['id']);
// Not a CMS page: no exception, so the next route (Front:Pages:search) can take it.
Assert::null($router->match(new Request(new UrlScript('http://example.test/pages/search/?s=a', '/'))));
// A page of another type, an unpublished page, and an unknown prefix don't match by prefix.
Assert::null($router->match(new Request(new UrlScript('http://example.test/news/other/', '/'))));
Assert::null($router->match(new Request(new UrlScript('http://example.test/news/hidden', '/'))));
Assert::null($router->match(new Request(new UrlScript('http://example.test/unknown/article', '/'))));
// slug/id still works when the first part is no prefix.
$match = $router->match(new Request(new UrlScript('http://example.test/article/42', '/')));
Assert::same(10, $match['page_id']);
Assert::same('42', $match['id']);
Assert::null($match['prefix']);
