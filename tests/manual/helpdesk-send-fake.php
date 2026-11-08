<?php
declare(strict_types=1);

// Opt-in local Docker check: render a real helpdesk row through Helpdesk::send()
// with a fake mailer. Nothing is delivered; prints a JSON summary.
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) {
    throw new RuntimeException('Run only in the local Docker app container.');
}
$id = $argv[1] ?? '';
if (!preg_match('/^[1-9][0-9]*$/D', $id)) {
    throw new InvalidArgumentException('Expected a helpdesk id.');
}
define('APP_DIR', __DIR__ . '/../../www');
$container = require __DIR__ . '/../../app/bootstrap.php';
$database = $container->getByType(Nette\Database\Explorer::class);
$mailer = new class implements Nette\Mail\Mailer {
    public ?Nette\Mail\Message $sent = null;
    public function send(Nette\Mail\Message $mail): void { $this->sent = $mail; }
};
$helpdesk = new App\Model\Helpdesk($database, $mailer);
$helpdesk->setId((int) $id);
$helpdesk->setSettings(['contacts:email:hq' => 'sender@example.invalid', 'site:title' => 'Local', 'site:url:base' => 'http://localhost:8090']);
$helpdesk->setEmail('synthetic@example.invalid');
$helpdesk->setParams(['name' => 'Fake Sender', 'phone' => '1', 'email' => 'fake@example.invalid', 'message' => 'Long enough',
    'url' => 'https://example.invalid/x', 'settings' => ['site:title' => 'Local', 'site:url:base' => 'http://localhost:8090'],
    'ipaddress' => '127.0.0.1', 'time' => date('Y-m-d H:i:s')]);
$sent = $helpdesk->send();
echo json_encode([
    'sent' => $sent,
    'subject' => $mailer->sent ? $mailer->sent->getSubject() : null,
    'body' => $mailer->sent ? $mailer->sent->getHtmlBody() : null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
