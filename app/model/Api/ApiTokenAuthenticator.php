<?php
declare(strict_types=1);

namespace App\Model\Api;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class ApiTokenAuthenticator
{
    private Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function authenticate(?string $authorizationHeader): ?ApiTokenIdentity
    {
        $token = $this->extractBearerToken($authorizationHeader);

        if ($token === null) {
            return null;
        }

        $hash = $this->hashToken($token);
        $tokenRow = $this->database->table('api_tokens')
            ->where('token_hash', $hash)
            ->where('revoked_at IS NULL')
            ->fetch();

        if (!$tokenRow || $this->isExpired($tokenRow)) {
            return null;
        }

        $user = $tokenRow->ref('users', 'users_id');

        if (!$user || (int) $user->state !== 1) {
            return null;
        }

        $tokenRow->update(['last_used_at' => date('Y-m-d H:i:s')]);

        return new ApiTokenIdentity($user, $tokenRow, $this->parseScopes((string) $tokenRow->scopes));
    }

    public function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function extractBearerToken(?string $authorizationHeader): ?string
    {
        if (!$authorizationHeader) {
            return null;
        }

        if (stripos($authorizationHeader, 'Bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($authorizationHeader, 7));
        return $token !== '' ? $token : null;
    }

    private function isExpired(ActiveRow $tokenRow): bool
    {
        if (!$tokenRow->expires_at) {
            return false;
        }

        return strtotime((string) $tokenRow->expires_at) < time();
    }

    /**
     * @return string[]
     */
    private function parseScopes(string $scopes): array
    {
        $items = array_map('trim', explode(',', $scopes));
        return array_values(array_filter($items, static function (string $scope): bool {
            return $scope !== '';
        }));
    }
}
