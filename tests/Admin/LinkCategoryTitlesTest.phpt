<?php
declare(strict_types=1);

// Taskino adc44a77: on an installation whose `links` table has no foreign key to links_categories,
// /admin/links/ stopped rendering at the first link: $link->links_categories needs that
// key. Output had already started, so the page ended there, without its scripts, and the
// category tree stayed a plain list without its context menu. The page now looks the
// category titles up by id.

use App\AdminModule\Presenters\LinksPresenter;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
// The application discovers references from foreign keys (the database extension's default).
$plain = createTestDatabase();
$db = new Nette\Database\Explorer($plain->getConnection(), $plain->getStructure(),
    new Nette\Database\Conventions\DiscoveredConventions($plain->getStructure()), new Nette\Caching\Storages\MemoryStorage);
// As on such an installation: no REFERENCES from links to links_categories.
$db->query('CREATE TABLE links_categories (id INTEGER PRIMARY KEY, parent_id INTEGER, description TEXT, title TEXT NOT NULL, sorted INTEGER DEFAULT 0)');
$db->query('CREATE TABLE links (id INTEGER PRIMARY KEY, links_categories_id INTEGER, url TEXT, title TEXT NOT NULL, description TEXT)');
$db->query("INSERT INTO links_categories (id, title) VALUES (1, 'Odkazy'), (2, 'Partneři')");
foreach ([[1, 1, 'Noise machine'], [2, 2, 'Partner'], [3, null, 'Bez kategorie'], [4, 9, 'Smazaná kategorie']] as [$id, $category, $title]) {
    $db->table('links')->insert(['id' => $id, 'links_categories_id' => $category, 'title' => $title]);
}

// What the old cell did: without the foreign key the reference throws.
Assert::exception(static function () use ($db): void {
    $db->table('links')->get(1)->links_categories;
}, Nette\MemberAccessException::class, "Cannot read an undeclared column 'links_categories'%a%");

// The list gets every category title by id; a missing or deleted category is an empty cell.
$mailer = new class implements Nette\Mail\Mailer {
    public function send(Nette\Mail\Message $mail): void {}
};
$presenter = new LinksPresenter($db, $mailer);
// The application's template class (TemplateFactory), which takes the presenter's variables.
$template = new DefaultTemplate(new Latte\Engine);
$property = new ReflectionProperty(Nette\Application\UI\Control::class, 'template');
$property->setAccessible(true);
$property->setValue($presenter, $template);
$presenter->renderDefault();
Assert::same([1 => 'Odkazy', 2 => 'Partneři'], $template->categoryTitles);
$cells = [];
foreach ($template->links as $link) {
    $cells[$link->title] = $template->categoryTitles[$link->links_categories_id] ?? '';
}
Assert::same(['Bez kategorie' => '', 'Noise machine' => 'Odkazy', 'Partner' => 'Partneři', 'Smazaná kategorie' => ''], $cells);

// The template uses that lookup and still compiles; nothing in the app reads a link's
// category through the reference any more.
$app = __DIR__ . '/../../app';
$file = $app . '/AdminModule/templates/Links/default.latte';
Assert::contains("<td>{\$categoryTitles[\$item->links_categories_id] ?? ''}</td>", file_get_contents($file));
$latte = new Latte\Engine;
Nette\Bridges\ApplicationLatte\UIMacros::install($latte->getCompiler());
Assert::contains('categoryTitles', $latte->compile($file));
$offenders = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($app, FilesystemIterator::SKIP_DOTS));
foreach ($files as $source) {
    if (in_array($source->getExtension(), ['latte', 'php'], true)
        && preg_match('~->links_categories\b~', file_get_contents($source->getPathname()))) {
        $offenders[] = substr($source->getPathname(), strlen($app) + 1);
    }
}
Assert::same([], $offenders);
