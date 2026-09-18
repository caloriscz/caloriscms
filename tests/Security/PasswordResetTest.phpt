<?php
declare(strict_types=1);

use App\Security\PasswordReset;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';

$db = createTestDatabase();
foreach (['email TEXT', 'password TEXT', 'activation TEXT', 'reset_token_hash TEXT', 'reset_expires_at TEXT'] as $column) {
    $db->query('ALTER TABLE users ADD COLUMN ' . $column);
}
$initial = password_hash('OriginalPassword1!', PASSWORD_BCRYPT);
$db->table('users')->where('id', 1)->update(['email' => 'owner@example.invalid', 'password' => $initial]);
$db->table('users')->where('id', 2)->update(['email' => 'disabled@example.invalid', 'password' => $initial]);
$service = new PasswordReset($db);
$now = time();
$token = $service->issue(1, $now);
Assert::match('1.%a%', $token);
$stored = $db->table('users')->get(1);
Assert::same(64, strlen($stored->reset_token_hash));
Assert::notSame(explode('.', $token)[1], $stored->reset_token_hash);
Assert::same($initial, $stored->password);
Assert::null($service->issue(1, $now + 30));
Assert::null($service->issue(2, $now));
Assert::null($service->issue(9999, $now));
foreach (['', '1.', '1.legacycode', '2.' . explode('.', $token)[1], $token . 'x'] as $bad) {
    Assert::false($service->consume($bad, 'NewPassword1!', $now));
}
Assert::false($service->consume($token, 'short', $now));
Assert::false($service->consume($token, str_repeat('a', 73), $now));
Assert::false($service->consume($token, 'NewPassword1!', $now + 3600));
Assert::same($initial, $db->table('users')->get(1)->password);
Assert::true($service->consume($token, 'NewPassword1!', $now + 3599));
Assert::true(password_verify('NewPassword1!', $db->table('users')->get(1)->password));
Assert::null($db->table('users')->get(1)->reset_token_hash);
Assert::false($service->consume($token, 'AnotherPassword1!', $now));

$token = $service->issue(1, $now);
$replacement = $service->issue(1, $now + 61);
Assert::false($service->consume($token, 'NewPassword2!', $now + 62));
Assert::true($service->consume($replacement, 'NewPassword2!', $now + 62));
foreach (['email' => 'changed@example.invalid', 'password' => $initial, 'state' => 0] as $field => $value) {
    $token = $service->issue(1, $now);
    $previous = $db->table('users')->get(1)->toArray();
    $db->table('users')->where('id', 1)->update([$field => $value]);
    Assert::false($service->consume($token, 'MustNotChange1!', $now));
    $db->table('users')->where('id', 1)->update($previous + []);
    $db->table('users')->where('id', 1)->update(['reset_token_hash' => null, 'reset_expires_at' => null]);
}
$db->table('users')->where('id', 2)->update(['email' => 'owner@example.invalid']);
Assert::null($service->issue(1));
$db->table('users')->where('id', 2)->update(['email' => 'disabled@example.invalid']);

$mailer = new class implements Mailer {
    public array $messages = [];
    public bool $fail = false;
    public function send(Message $message): void {
        if ($this->fail) { throw new RuntimeException('Synthetic delivery failure'); }
        $this->messages[] = $message;
    }
};
$settings = ['site:url:base' => 'https://cms.example.invalid/subdir', 'contacts:email:hq' => 'cms@example.invalid'];
Assert::false($service->send(1, $mailer, ['site:url:base' => 'http://attacker.invalid'] + $settings));
Assert::null($db->table('users')->get(1)->reset_token_hash);
Assert::true($service->send(1, $mailer, $settings));
Assert::count(1, $mailer->messages);
$message = $mailer->messages[0];
Assert::same(['owner@example.invalid' => null], $message->getHeader('To'));
Assert::contains('https://cms.example.invalid/subdir/admin/sign/resetpass#reset=1.', $message->getBody());
Assert::notContains('NewPassword', $message->getBody());
Assert::notContains($initial, $message->getBody());
Assert::false($service->send(1, $mailer, $settings));
Assert::count(1, $mailer->messages);
$db->table('users')->where('id', 1)->update(['reset_token_hash' => null, 'reset_expires_at' => null]);
$mailer->fail = true;
$before = $db->table('users')->get(1)->password;
Assert::false($service->send(1, $mailer, $settings));
Assert::null($db->table('users')->get(1)->reset_token_hash);
Assert::same($before, $db->table('users')->get(1)->password);
