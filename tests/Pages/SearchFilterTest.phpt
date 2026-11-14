<?php
declare(strict_types=1);

// Taskino 6584682f: /pages/search/ was a 500 on PHP 8, because Filter counted
// null columns, and unknown or array query values logged notices.

use App\Model\Filter;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
foreach ([[1, 'Úvod', '<p>Caloris CMS</p>', '2026-01-02', 1], [2, 'Blog', '<p>Novinky</p>', '2026-03-04', 1],
    [3, 'Caloris tips', '<p>Rady</p>', '2026-02-03', 1], [4, 'Caloris draft', '<p>Koncept</p>', '2026-04-05', 0]]
    as [$id, $title, $document, $published, $public]) {
    $db->table('pages')->insert(['id' => $id, 'slug' => 'p' . $id, 'title' => $title, 'document' => $document,
        'date_published' => $published, 'pages_types_id' => 1, 'public' => $public]);
}
$titles = static function (Filter $filter): array {
    $titles = [];
    foreach ($filter->assemble() as $row) {
        $titles[] = $row->title;
    }
    return $titles;
};

// The search page sets no columns: no TypeError, and the text matches titles and documents.
// The unpublished 'Caloris draft' matches the text too, but is never listed.
$filter = new Filter($db);
Assert::same([], $filter->getColumns());
$filter->setText('caloris');
$filter->setOrder(null);
Assert::same(['Caloris tips', 'Úvod'], $titles($filter));

// No GROUP BY: grouping by title and document while selecting other columns is error 1055
// under MySQL's ONLY_FULL_GROUP_BY, and it made the paginator count groups, not pages.
$filter = new Filter($db);
$filter->setText('caloris');
$filter->setOrder('na');
$selection = $filter->assemble();
Assert::notContains('GROUP BY', strtoupper($selection->getSql()));
Assert::same(2, $selection->count('*'));

// Known orders still apply; anything else falls back to title ascending.
foreach (['na' => '`title` ASC', 'dd' => '`date_published` DESC', 'oa' => '`sorted` ASC'] as $order => $sql) {
    $filter->setOrder($order);
    Assert::same($sql, $filter->getOrder());
}
foreach (['xyz', '', null, ['dd'], 7] as $order) {
    $filter->setOrder($order);
    Assert::same('`title` ASC', $filter->getOrder(), var_export($order, true));
}

$filter = new Filter($db);
$filter->setText(null);
$filter->setOrder('dd');
Assert::same(['Blog', 'Caloris tips', 'Úvod'], $titles($filter));

// Column conditions still filter when they are set.
$filter = new Filter($db);
$filter->setText(null);
$filter->setOrder('na');
$filter->setColumns(['pages.id' => 2]);
Assert::same(['Blog'], $titles($filter));
