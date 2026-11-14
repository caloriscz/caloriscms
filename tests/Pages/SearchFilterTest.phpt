<?php
declare(strict_types=1);

// Taskino 6584682f: /pages/search/ was a 500 on PHP 8, because Filter counted
// null columns, and unknown or array query values logged notices.

use App\Model\Filter;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
foreach ([[1, 'Úvod', '<p>Caloris CMS</p>', '2026-01-02'], [2, 'Blog', '<p>Novinky</p>', '2026-03-04'],
    [3, 'Caloris tips', '<p>Rady</p>', '2026-02-03']] as [$id, $title, $document, $published]) {
    $db->table('pages')->insert(['id' => $id, 'slug' => 'p' . $id, 'title' => $title, 'document' => $document,
        'date_published' => $published, 'pages_types_id' => 1, 'public' => 1]);
}
$titles = static function (Filter $filter): array {
    $titles = [];
    foreach ($filter->assemble() as $row) {
        $titles[] = $row->title;
    }
    return $titles;
};

// The search page sets no columns: no TypeError, and the text matches titles and documents.
$filter = new Filter($db);
Assert::same([], $filter->getColumns());
$filter->setText('caloris');
$filter->setOrder(null);
Assert::same(['Caloris tips', 'Úvod'], $titles($filter));

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
