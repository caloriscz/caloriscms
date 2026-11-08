<?php
declare(strict_types=1);
use App\Model\Helpdesk;
use Nette\Application\AbortException;
use Nette\Forms\BootstrapUIForm;
use Tester\Assert;
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
$db->query('CREATE TABLE helpdesk (id INTEGER PRIMARY KEY, email TEXT, subject TEXT, body TEXT, helpdesk_templates_id INTEGER, log INTEGER, pages_id INTEGER, blacklist INTEGER)');
$db->query('CREATE TABLE helpdesk_templates (id INTEGER PRIMARY KEY, document TEXT)');
$db->query('CREATE TABLE helpdesk_messages (id INTEGER PRIMARY KEY, message TEXT, helpdesk_id INTEGER, email TEXT, ipaddress TEXT, date_created TEXT, status INTEGER)');
$db->query('CREATE TABLE blacklist (id INTEGER PRIMARY KEY, title TEXT)');
$db->table('pages')->insert(['id' => 1, 'slug' => 'contact', 'title' => 'Contact']);
$config = ['id' => 1, 'email' => 'private-recipient@example.invalid', 'subject' => 'private subject',
    'body' => '<p>{$message}</p>', 'helpdesk_templates_id' => 1, 'log' => 0, 'pages_id' => 1, 'blacklist' => 0];
$db->table('helpdesk')->insert($config);
$db->table('helpdesk_templates')->insert(['id' => 1, 'document' => '<html><head><title>%TITLE%</title></head><body>%CONTENT%</body></html>']);
$logger = new class implements Tracy\ILogger {
    public array $entries = [];
    public function log($message, $level = self::INFO) { $this->entries[] = json_decode((string) $message, true); }
};
Tracy\Debugger::setLogger($logger);
$mailer = new class implements Nette\Mail\Mailer {
    public ?Throwable $failure = null;
    public int $attempts = 0;
    public function send(Nette\Mail\Message $mail): void { ++$this->attempts; if ($this->failure) { throw $this->failure; } }
};
$mail = new Helpdesk($db, $mailer);
$mail->setId(1);
$mail->setEmail('private-user@example.invalid');
$mail->setSettings(['contacts:email:hq' => 'private-sender@example.invalid']);
$mail->setParams(['message' => 'private body password=synthetic-secret']);
Assert::true($mail->send());
Assert::same([], $logger->entries);
Assert::same('/contact', $mail->getReturnPath());
foreach ([new Nette\Mail\SendException('SMTP password=synthetic-secret'), new Error('private internal transport detail')] as $failure) {
    $mailer->failure = $failure;
    Assert::false($mail->send());
    Assert::same(['event' => 'helpdesk.failure', 'operation' => 'transport', 'helpdesk_id' => 1,
        'template_id' => 1, 'category' => 'failed'], end($logger->entries));
}
Assert::same(0, $db->table('helpdesk_messages')->count('*'));
$mailer->failure = null;
$attempts = $mailer->attempts;
$db->table('helpdesk')->get(1)->update(['helpdesk_templates_id' => 999]);
Assert::false($mail->send());
Assert::same('render', end($logger->entries)['operation']);
$db->table('helpdesk')->get(1)->update(['helpdesk_templates_id' => 1, 'body' => '{invalidMacro}']);
Assert::false($mail->send());
$db->table('helpdesk')->get(1)->update(['body' => $config['body']]);
$mail->setSettings([]);
Assert::false($mail->send());
Assert::same('message', end($logger->entries)['operation']);
Assert::same($attempts, $mailer->attempts);
$mail->setSettings(['contacts:email:hq' => 'private-sender@example.invalid']);
$db->table('helpdesk')->get(1)->update(['log' => 1]);
$db->query("CREATE TRIGGER reject_archive BEFORE INSERT ON helpdesk_messages BEGIN SELECT RAISE(ABORT, 'synthetic-secret'); END");
Assert::true($mail->send()); // Archive failure must not prompt duplicate delivery.
Assert::same('archive', end($logger->entries)['operation']);
$db->query('DROP TRIGGER reject_archive');
Assert::true($mail->send());
Assert::same(1, $db->table('helpdesk_messages')->count('*'));
$db->table('helpdesk')->get(1)->delete();
Assert::false($mail->send());
Assert::same('missing_helpdesk', end($logger->entries)['category']);
Assert::same('/', $mail->getReturnPath());
$db->table('helpdesk')->insert($config);
$db->table('pages')->get(1)->delete();
Assert::same('/', $mail->getReturnPath());

class HelpdeskTestTemplate extends Nette\Bridges\ApplicationLatte\Template { public array $settings = ['contacts:email:hq' => 'private-sender@example.invalid']; }
$factory = new class implements Nette\Application\UI\TemplateFactory {
    public function createTemplate(?Nette\Application\UI\Control $control = null): Nette\Application\UI\Template { return new HelpdeskTestTemplate(new Latte\Engine); }
};
$presenter = new class extends Nette\Application\UI\Presenter {
    public Nette\Mail\Mailer $mailer;
    public array $messages = [];
    public string $location = '';
    // No session in CLI tests. Like the real Presenter::getFlashSession(), the
    // first flash generates the key into the _fid parameter; the redirect must
    // carry it (redirectUrl() alone never does, which hid the lost feedback).
    public function flashMessage($message, string $type = 'info'): stdClass {
        $this->params[self::FLASH_KEY] = $this->params[self::FLASH_KEY] ?? 'tst1';
        $flash = (object) ['message' => $message, 'type' => $type]; $this->messages[] = $flash; return $flash;
    }
    public function redirectUrl(string $url, ?int $httpCode = null): void { $this->location = $url; throw new AbortException; }
};
$presenter->injectPrimary(null, null, null, new Nette\Http\Request(new Nette\Http\UrlScript('http://localhost/contact')),
    new Nette\Http\Response, null, null, $factory);
$presenter->mailer = $mailer;
$control = new App\Forms\Helpdesk\HelpdeskControl($db);
$control->setParent($presenter, 'helpdesk');
$form = static function (array $changes = []): BootstrapUIForm {
    $form = new class extends BootstrapUIForm { public function getHttpData(?int $type = null, ?string $htmlName = null) { return []; } };
    foreach (array_merge(['name' => 'Private User', 'email' => 'private-user@example.invalid', 'phone' => '', 'message' => 'private body'], $changes) as $key => $value) { $form->addText($key)->setDefaultValue($value); }
    return $form;
};
foreach ([['name' => ''], ['email' => 'invalid'], ['message' => '']] as $changes) {
    $invalid = $form($changes);
    Assert::exception(function () use ($control, $invalid): void { $control->sendFormValidated($invalid); }, AbortException::class);
    Assert::true(count($invalid->getErrors()) > 0);
    Assert::same('/?_fid=tst1', $presenter->location);
    Assert::same('error', end($presenter->messages)->type);
}
$db->table('helpdesk')->get(1)->update(['blacklist' => 1]);
$db->table('blacklist')->insert(['title' => 'blocked']);
Assert::exception(function () use ($control, $form): void { $control->sendFormValidated($form(['message' => 'blocked'])); }, AbortException::class);
$db->table('helpdesk')->get(1)->delete();
Assert::exception(function () use ($control, $form): void { $control->sendFormValidated($form()); }, AbortException::class);
$db->table('helpdesk')->insert($config);
$db->table('pages')->insert(['id' => 1, 'slug' => 'contact', 'title' => 'Contact']);
foreach ([null, new Nette\Mail\SendException('synthetic-secret')] as $failure) {
    $mailer->failure = $failure;
    $valid = $form();
    $control->sendFormValidated($valid);
    Assert::same([], $valid->getErrors());
    Assert::exception(function () use ($control, $valid): void { $control->sendFormSucceeded($valid); }, AbortException::class);
    Assert::same('/contact?_fid=tst1', $presenter->location);
    Assert::same($failure ? 'error' : 'success', end($presenter->messages)->type);
    Assert::true(end($presenter->messages)->helpdesk);
}
// A stored slug can never turn the return path into another host.
foreach (["/\t/evil.example", "\\\\evil.example", "/\r\n/evil.example", ' //evil.example'] as $slug) {
    $db->table('pages')->get(1)->update(['slug' => $slug]);
    Assert::same('/evil.example', $mail->getReturnPath());
}
$feedback = (new Latte\Engine)->renderToString(__DIR__ . '/../../app/FrontModule/templates/HelpdeskFeedback.latte', [
    'flashes' => [(object) ['helpdesk' => true, 'type' => 'error', 'message' => '<script>unsafe</script>'],
        (object) ['type' => 'info', 'message' => 'unrelated message']],
]);
Assert::contains('&lt;script&gt;unsafe&lt;/script&gt;', $feedback);
Assert::notContains('<script>', $feedback);
Assert::notContains('unrelated message', $feedback);
$mail->setId("synthetic-secret\ninjected");
$mail->reportFailure('synthetic-secret', 'synthetic-secret');
Assert::null(end($logger->entries)['helpdesk_id']);
Assert::same('unknown', end($logger->entries)['operation']);
foreach (['private', 'synthetic-secret', '@', 'password', '{invalidMacro}'] as $secret) {
    Assert::notContains($secret, json_encode($logger->entries));
}
