<?php
declare(strict_types=1);

namespace App\Security;

use Nette\Database\Explorer;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Security\Passwords;
use Nette\Utils\Validators;

/** Password setup and recovery share one expiring, single-use credential. */
final class PasswordReset
{
    private Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function issue(int $userId, ?int $now = null): ?string
    {
        $now = $now ?? time();
        $user = $this->database->table('users')->get($userId);
        if (!$user || (int) $user->state !== 1 || !Validators::isEmail($user->email)
            || $this->database->table('users')->where('email', $user->email)->count() !== 1) {
            return null;
        }
        // Bound repeated recovery mail from any session to once per minute/user.
        if ($user->reset_expires_at && (string) $user->reset_expires_at > gmdate('Y-m-d H:i:s', $now + 3540)) {
            return null;
        }
        $secret = bin2hex(random_bytes(32));
        $hash = self::digest($secret, $user->email, $user->password);
        $updated = $this->database->table('users')->where([
            'id' => $userId, 'state' => 1, 'email' => $user->email, 'password' => $user->password,
            'reset_token_hash' => $user->reset_token_hash,
        ])->update(['reset_token_hash' => $hash, 'reset_expires_at' => gmdate('Y-m-d H:i:s', $now + 3600)]);
        return $updated === 1 ? $userId . '.' . $secret : null;
    }

    public function consume(string $token, string $password, ?int $now = null): bool
    {
        if (strlen($password) < 8 || strlen($password) > 72 || !preg_match('/^([1-9][0-9]*)\.([a-f0-9]{64})$/D', $token, $parts)) {
            return false;
        }
        $user = $this->database->table('users')->get($parts[1]);
        if (!$user || (int) $user->state !== 1 || !$user->reset_token_hash) {
            return false;
        }
        $hash = self::digest($parts[2], $user->email, $user->password);
        if (!hash_equals($user->reset_token_hash, $hash)) {
            return false;
        }
        // Atomic compare-and-update: only one concurrent request can consume it.
        return $this->database->table('users')->where([
            'id' => $user->id, 'state' => 1, 'email' => $user->email,
            'password' => $user->password, 'reset_token_hash' => $hash,
        ])->where('reset_expires_at > ?', gmdate('Y-m-d H:i:s', $now ?? time()))->update([
            'password' => (new Passwords())->hash($password),
            'reset_token_hash' => null, 'reset_expires_at' => null, 'activation' => null,
        ]) === 1;
    }

    public function send(int $userId, Mailer $mailer, array $settings): bool
    {
        // Use configured site origin, never the untrusted request Host header.
        $base = rtrim((string) ($settings['site:url:base'] ?? ''), '/');
        $url = parse_url($base);
        $from = (string) ($settings['contacts:email:hq'] ?? '');
        if (!$url || !isset($url['scheme'], $url['host']) || isset($url['user']) || isset($url['pass'])
            || isset($url['query']) || isset($url['fragment']) || !Validators::isEmail($from)
            || ($url['scheme'] !== 'https' && !($url['scheme'] === 'http' && in_array($url['host'], ['localhost', '127.0.0.1'], true)))) {
            return false;
        }
        $token = $this->issue($userId);
        if ($token === null) {
            return false;
        }
        $user = $this->database->table('users')->get($userId);
        try {
            $mail = new Message();
            $mail->setFrom($from)->addTo($user->email)->setSubject('Nastavení hesla');
            $mail->setBody("Pro nastavení hesla otevřete odkaz do jedné hodiny:\n\n"
                . $base . '/admin/sign/resetpass#reset=' . $token
                . "\n\nOdkaz lze použít pouze jednou. Pokud jste změnu nežádali, zprávu ignorujte.\n");
            // Deliberately bypass Helpdesk: its message log persists full bodies.
            $mailer->send($mail);
            return true;
        } catch (\Throwable $e) {
            // Never log a mail exception/body that could contain the credential.
            $parts = explode('.', $token, 2);
            $this->database->table('users')->where([
                'id' => $userId, 'reset_token_hash' => self::digest($parts[1], $user->email, $user->password),
            ])->update(['reset_token_hash' => null, 'reset_expires_at' => null]);
            return false;
        }
    }

    private static function digest(string $secret, string $email, string $passwordHash): string
    {
        // Changing the recipient or password invalidates an outstanding link.
        return hash('sha256', $secret . "\0" . $email . "\0" . $passwordHash);
    }
}
