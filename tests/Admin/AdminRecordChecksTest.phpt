<?php
declare(strict_types=1);

// Task 92bd3229: missing or non-numeric admin ids are a 404, and edit forms
// refuse values longer than their column (strict MariaDB made them a 500).

use App\Model\Helpdesk;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Forms\BootstrapUIForm;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
$db->query('CREATE TABLE menu_menus (id INTEGER PRIMARY KEY, title TEXT, description TEXT, type TEXT, class TEXT)');
$db->query('CREATE TABLE helpdesk (id INTEGER PRIMARY KEY, subject TEXT, body TEXT, email TEXT, helpdesk_templates_id INTEGER, pages_id INTEGER, log INTEGER, blacklist INTEGER)');
$db->query('CREATE TABLE helpdesk_templates (id INTEGER PRIMARY KEY, document TEXT)');
$db->table('menu_menus')->insert(['id' => 7, 'title' => 'Top', 'type' => 'Menu', 'class' => 'nav']);
$db->table('helpdesk')->insert(['id' => 5, 'subject' => 'Poptávka', 'body' => '<p>x</p>', 'helpdesk_templates_id' => 1, 'log' => 0, 'blacklist' => 0]);
$db->table('helpdesk_templates')->insert(['id' => 1, 'document' => '<title>%TITLE%</title>%CONTENT%']);

// requireRecord(): the row for a positive integer id, a 404 for anything else.
$mailer = new class implements Nette\Mail\Mailer {
    public function send(Nette\Mail\Message $mail): void {}
};
$admin = new class($db, $mailer) extends App\AdminModule\Presenters\BasePresenter {
    public function record(string $table, $id) { $this->params = ['id' => $id]; return $this->requireRecord($table); }
};
Assert::same('Top', $admin->record('menu_menus', '7')->title);
Assert::same(7, $admin->record('menu_menus', 7)->id);
foreach ([null, '', '999999', 'abc', '[object Event]', '7abc', ' 7', '07', '0', '-7', '7.0', ['7'], '99999999999999999999'] as $id) {
    $error = Assert::exception(static function () use ($admin, $id): void { $admin->record('menu_menus', $id); }, BadRequestException::class);
    Assert::same(404, $error->getHttpCode(), var_export($id, true));
}

$presenter = new class extends Nette\Application\UI\Presenter {
    public array $redirects = [];
    public function redirect(string $destination, $args = []): void { $this->redirects[] = $args; throw new AbortException; }
};
$form = static function (array $values): BootstrapUIForm {
    $form = new class extends BootstrapUIForm {
        public function getHttpData(?int $type = null, ?string $htmlName = null) { return []; }
    };
    foreach ($values as $key => $value) { $form->addText($key)->setDefaultValue($value); }
    return $form;
};
$save = static function ($control, string $method, BootstrapUIForm $form) use ($presenter): void {
    $control->setParent($presenter, 'writer');
    try { $control->$method($form); } catch (AbortException $expected) {
    } finally { $control->setParent(null); }
};

// Menu group edit: menu_menus.title is varchar(30), class varchar(60).
$group = static function (array $values) use ($form): BootstrapUIForm {
    return $form($values + ['id' => 7, 'title' => 'Top', 'description' => '', 'type' => 'Menu', 'class' => 'nav']);
};
foreach ([['title' => str_repeat('m', 31)], ['class' => str_repeat('c', 61)], ['title' => str_repeat('ž', 31)]] as $values) {
    $rejected = $group($values);
    $save(new App\Forms\Menu\MenuMenusEditControl($db), 'editFormSucceeded', $rejected);
    Assert::count(1, $rejected->getErrors(), json_encode($values));
    Assert::contains('Nic nebylo uloženo.', $rejected->getErrors()[0]);
    Assert::same(['Top', 'nav'], [$db->table('menu_menus')->get(7)->title, $db->table('menu_menus')->get(7)->class]);
}
$both = $group(['title' => str_repeat('m', 31), 'class' => str_repeat('c', 61)]);
$save(new App\Forms\Menu\MenuMenusEditControl($db), 'editFormSucceeded', $both);
Assert::same(['Název menu může mít nejvýše 30 znaků. Nic nebylo uloženo.',
    'Třída pro CSS styl může mít nejvýše 60 znaků. Nic nebylo uloženo.'], $both->getErrors());
// The limits count characters, not bytes: 30 Czech letters and a 60-character class fit.
$fits = $group(['title' => str_repeat('ž', 30), 'class' => str_repeat('c', 60), 'description' => 'Popis']);
$save(new App\Forms\Menu\MenuMenusEditControl($db), 'editFormSucceeded', $fits);
Assert::same([], $fits->getErrors());
Assert::same([str_repeat('ž', 30), str_repeat('c', 60), 'Popis'],
    [$db->table('menu_menus')->get(7)->title, $db->table('menu_menus')->get(7)->class, $db->table('menu_menus')->get(7)->description]);
// A forged hidden id names no row: a 404, not a call on null.
Assert::exception(static function () use ($save, $group, $db): void {
    $save(new App\Forms\Menu\MenuMenusEditControl($db), 'editFormSucceeded', $group(['id' => 999999]));
}, BadRequestException::class);

// Helpdesk subject: helpdesk.subject is varchar(250).
Assert::null(Helpdesk::subjectError(str_repeat('ž', 250)));
Assert::same('Předmět může mít nejvýše 250 znaků (má 251). Nic nebylo uloženo.', Helpdesk::subjectError(str_repeat('s', 251)));
// The {syntax} rule still applies to subjects that fit.
Assert::contains('syntaxi Latte', Helpdesk::subjectError('{syntax double}'));
$long = $form(['id' => 5, 'subject' => str_repeat('s', 251), 'body' => '<p>changed</p>']);
$save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $long);
Assert::same(['Předmět může mít nejvýše 250 znaků (má 251). Nic nebylo uloženo.'], $long->getErrors());
Assert::same(['Poptávka', '<p>x</p>'], [$db->table('helpdesk')->get(5)->subject, $db->table('helpdesk')->get(5)->body]);
$fits = $form(['id' => 5, 'subject' => str_repeat('ž', 250), 'body' => '<p>changed</p>']);
$save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $fits);
Assert::same([], $fits->getErrors());
Assert::same(str_repeat('ž', 250), $db->table('helpdesk')->get(5)->subject);
Assert::exception(static function () use ($save, $form, $db): void {
    $save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded',
        $form(['id' => 999999, 'subject' => 'x', 'body' => '<p>x</p>']));
}, BadRequestException::class);

// Saving a form's settings returns to that form, not to the page named by its template id.
$settings = new App\Forms\Helpdesk\EditHelpdeskEmailSettingsControl($db);
$saved = [];
$settings->onSave[] = static function ($id) use (&$saved): void { $saved[] = $id; };
$settingsForm = $form(['helpdesk_id' => 5, 'helpdesk_templates_id' => 1, 'pages_id' => 0, 'email' => 'x@example.invalid', 'log' => 1, 'blacklist' => 0]);
$settings->editFormSucceeded($settingsForm);
Assert::same(['5'], array_map('strval', $saved));
Assert::same('x@example.invalid', $db->table('helpdesk')->get(5)->email);

// Nette 3 has no $baseUri; every template uses $basePath.
$templates = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../app', FilesystemIterator::SKIP_DOTS));
$uses = [];
foreach ($templates as $file) {
    if ($file->getExtension() === 'latte' && strpos(file_get_contents($file->getPathname()), '$baseUri') !== false) {
        $uses[] = $file->getPathname();
    }
}
Assert::same([], $uses);
