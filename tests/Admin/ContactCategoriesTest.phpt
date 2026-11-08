<?php
declare(strict_types=1);

// Task 885bb1fb: contact category management restored (owner decision of 2026-11-08).
// Edit reads and writes contacts_categories, a category can't move into its own
// subtree, and delete refuses a category with contacts (contacts.contacts_categories_id
// cascades on delete in MariaDB) or subcategories.

use App\Forms\Contacts\EditCategoryControl;
use App\Forms\Contacts\InsertCategoryControl;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Forms\BootstrapUIForm;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
$db->query('CREATE TABLE contacts_categories (id INTEGER PRIMARY KEY, parent_id INTEGER, description TEXT, title TEXT NOT NULL)');
$db->query('CREATE TABLE contacts (id INTEGER PRIMARY KEY, name TEXT, contacts_categories_id INTEGER REFERENCES contacts_categories(id) ON DELETE CASCADE)');
// 1 Členové > 2 Výbor > 3 Revize; 4 Newsletter (one contact); 5 Prázdná
foreach ([[1, null, 'Členové'], [2, 1, 'Výbor'], [3, 2, 'Revize'], [4, null, 'Newsletter'], [5, null, 'Prázdná']] as [$id, $parent, $title]) {
    $db->table('contacts_categories')->insert(['id' => $id, 'parent_id' => $parent, 'title' => $title]);
}
$db->table('contacts')->insert(['id' => 1, 'name' => 'Odběratel', 'contacts_categories_id' => 4]);

$presenter = new class extends Nette\Application\UI\Presenter {
    public array $flashes = [];
    public function redirect(string $destination, $args = []): void { throw new AbortException; }
    public function flashMessage($message, string $type = 'info'): \stdClass
    {
        $this->flashes[] = [$type, $message];
        return (object) ['message' => $message, 'type' => $type];
    }
};
$form = static function (array $values): BootstrapUIForm {
    $form = new class extends BootstrapUIForm {
        public function getHttpData(?int $type = null, ?string $htmlName = null) { return []; }
    };
    foreach ($values as $key => $value) { $form->addText($key)->setDefaultValue($value); }
    return $form;
};
$call = static function ($control, string $method, BootstrapUIForm $form) use ($presenter): void {
    $control->setParent($presenter, 'writer');
    try { $control->$method($form); } catch (AbortException $expected) {
    } finally { $control->setParent(null); }
};
$row = static function (int $id) use ($db): array {
    $row = $db->table('contacts_categories')->get($id);
    return [$row->title, $row->parent_id];
};

// The subtree of a category: itself and every level below it.
$edit = new EditCategoryControl($db);
Assert::same([1, 2, 3], $edit->subtreeIds(1));
Assert::same([2, 3], $edit->subtreeIds(2));
Assert::same([4], $edit->subtreeIds(4));

// Rejected edits write nothing.
foreach ([
    'empty name' => [['id' => 2, 'title' => '  ', 'parent' => 1], 'musí mít název'],
    '81 characters' => [['id' => 2, 'title' => str_repeat('ž', 81), 'parent' => 1], 'nejvýše 80 znaků'],
    'under itself' => [['id' => 1, 'title' => 'Členové', 'parent' => 1], 'pod ni samotnou'],
    'under its subcategory' => [['id' => 1, 'title' => 'Členové', 'parent' => 3], 'pod ni samotnou'],
    'sibling with that name' => [['id' => 5, 'title' => 'Newsletter', 'parent' => null], 'už na tomto místě existuje'],
] as $case => [$values, $message]) {
    $before = $row($values['id']);
    $rejected = $form($values);
    $call(new EditCategoryControl($db), 'editFormSucceeded', $rejected);
    Assert::count(1, $rejected->getErrors(), $case);
    Assert::contains($message, $rejected->getErrors()[0], $case);
    Assert::same($before, $row($values['id']), $case);
}
// A rename, a move under another category and back to the top level are saved.
$ok = $form(['id' => 5, 'title' => str_repeat('ž', 80), 'parent' => 4]);
$call(new EditCategoryControl($db), 'editFormSucceeded', $ok);
Assert::same([], $ok->getErrors());
Assert::same([str_repeat('ž', 80), 4], $row(5));
$ok = $form(['id' => 5, 'title' => 'Prázdná', 'parent' => null]);
$call(new EditCategoryControl($db), 'editFormSucceeded', $ok);
Assert::same(['Prázdná', null], $row(5));
// The same name under another parent is fine.
$ok = $form(['id' => 3, 'title' => 'Výbor', 'parent' => 2]);
$call(new EditCategoryControl($db), 'editFormSucceeded', $ok);
Assert::same([], $ok->getErrors());
// A forged id is a 404.
Assert::exception(static function () use ($call, $form, $db): void {
    $call(new EditCategoryControl($db), 'editFormSucceeded', $form(['id' => 99, 'title' => 'x', 'parent' => null]));
}, BadRequestException::class);

// Adding: a duplicate at the top level is now caught ('' never matched NULL), and so
// are an over-long name and a missing parent; nothing is inserted.
$count = static function () use ($db): int { return $db->table('contacts_categories')->count('*'); };
foreach ([
    [['parent' => '', 'title' => 'Newsletter'], 'již existuje'],
    [['parent' => '1', 'title' => 'Výbor'], 'již existuje'],
    [['parent' => '', 'title' => str_repeat('x', 81)], 'nejvýše 80 znaků'],
    [['parent' => '', 'title' => ' '], 'musí mít název'],
    [['parent' => '99', 'title' => 'Sirotek'], 'neexistuje'],
] as [$values, $message]) {
    $presenter->flashes = [];
    $before = $count();
    $call(new InsertCategoryControl($db), 'validateFormSucceeded', $form($values));
    Assert::contains($message, $presenter->flashes[0][1] ?? '', json_encode($values, JSON_UNESCAPED_UNICODE));
    Assert::same($before, $count());
}
// Valid names pass validation: a new top-level one, and an existing name under another parent.
foreach ([['parent' => '', 'title' => 'Dodavatelé'], ['parent' => '4', 'title' => 'Výbor']] as $values) {
    $presenter->flashes = [];
    $call(new InsertCategoryControl($db), 'validateFormSucceeded', $form($values));
    Assert::same([], $presenter->flashes);
}

// Delete refuses a category with contacts or subcategories and removes an empty one.
$contacts = new class($db, new class implements Nette\Mail\Mailer {
    public function send(Nette\Mail\Message $mail): void {}
}) extends App\AdminModule\Presenters\ContactsPresenter {
    public array $flashes = [];
    public function redirect(string $destination, $args = []): void { throw new AbortException; }
    public function flashMessage($message, string $type = 'info'): \stdClass
    {
        $this->flashes[] = [$type, $message];
        return (object) ['message' => $message, 'type' => $type];
    }
};
$delete = static function ($id) use ($contacts): array {
    $contacts->flashes = [];
    try { $contacts->handleDeleteCategory($id); } catch (AbortException $expected) {}
    return $contacts->flashes[0] ?? [];
};
[$type, $message] = $delete(4);
Assert::same('error', $type);
Assert::contains('obsahuje kontakty (1)', $message);
Assert::notNull($db->table('contacts_categories')->get(4));
Assert::notNull($db->table('contacts')->get(1));
[$type, $message] = $delete('2');
Assert::same('error', $type);
Assert::contains('má podkategorie', $message);
Assert::notNull($db->table('contacts_categories')->get(2));
[$type, $message] = $delete('5');
Assert::same(['success', 'Kategorie „Prázdná“ byla smazána.'], [$type, $message]);
Assert::null($db->table('contacts_categories')->get(5));
foreach (['99', 'abc', '0'] as $id) {
    Assert::exception(static function () use ($delete, $id): void { $delete($id); }, BadRequestException::class);
}
