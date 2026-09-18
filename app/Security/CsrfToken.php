<?php
declare(strict_types=1);

namespace App\Security;

final class CsrfToken
{
    private const TOKEN_BYTES = 32;

    public static function generate(): string
    {
        return bin2hex(random_bytes(self::TOKEN_BYTES));
    }

    public static function validatesRequest(string $method, $submittedToken, string $expectedToken): bool
    {
        return strcasecmp($method, 'POST') === 0
            && is_string($submittedToken)
            && hash_equals($expectedToken, $submittedToken);
    }
}
