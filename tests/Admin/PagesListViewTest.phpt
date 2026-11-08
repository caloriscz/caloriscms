<?php
declare(strict_types=1);

// Task c260ce18 (owner decision of 2026-11-08, option A): the Pages "Strom" view is
// removed. It never rendered with subpages (endless recursion), carried a menu insert
// form that only needed the "pages" grant, and its .tree was wired to menu editor
// mutations. A stored 'tree' view shows the list; jsTree is bound only to the menu editor.

use Caloriscz\Page\PageListControl;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';

// Only the two remaining views are kept; anything else (an old 'tree' cookie too) is the list.
Assert::same(['simple', 'gallery'], PageListControl::VIEWS);
foreach (['simple' => 'simple', 'gallery' => 'gallery'] as $stored => $view) {
    Assert::same($view, PageListControl::normalizeView($stored));
}
foreach (['tree', null, '', false, 'Gallery', ' simple', '<script>x</script>', ['gallery'], 1] as $stored) {
    Assert::same('simple', PageListControl::normalizeView($stored), var_export($stored, true));
}

$list = new PageListControl(createTestDatabase());
Assert::same('simple', $list->setView('tree'));
Assert::same('gallery', $list->setView('gallery'));
// The nested menu insert form is gone with the tree view.
Assert::null($list->getComponent('menuInsert', false));

$app = __DIR__ . '/../../app';
Assert::false(file_exists($app . '/components/Page/PageListTreeControl.latte'));
Assert::notContains('Tree', file_get_contents($app . '/components/Page/PageListControl.php'));
Assert::notContains("'tree'", file_get_contents($app . '/AdminModule/templates/Pages/default.latte'));

// The menu editor mutations belong to the menu editor's own tree, and only its template
// carries that class.
$js = file_get_contents(__DIR__ . '/../../www/js/caloris-admin.js');
$start = strpos($js, "$('.menu-editor-tree').jstree({");
Assert::type('int', $start);
$end = strpos($js, '/* JSTree', $start);
Assert::type('int', $end);
Assert::same(1, preg_match_all('/\.jstree\(\{/', substr($js, $start, $end - $start)));
preg_match_all("/do: 'menuEditor-/", $js, $mutations, PREG_OFFSET_CAPTURE);
Assert::count(4, $mutations[0]);
foreach ($mutations[0] as [, $offset]) {
    Assert::true($offset > $start && $offset < $end);
}
Assert::same(0, preg_match('/\$\(\s*[\'"]\.tree[\'"]\s*\)/', $js), 'no handler on every .tree');

$owners = [];
$templates = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($app, FilesystemIterator::SKIP_DOTS));
foreach ($templates as $file) {
    if ($file->getExtension() === 'latte' && preg_match('/class="[^"]*\bmenu-editor-tree\b/', file_get_contents($file->getPathname()))) {
        $owners[] = str_replace('\\', '/', substr($file->getPathname(), strlen($app) + 1));
    }
}
Assert::same(['components/Menus/MenuEditorControl.latte'], $owners);
