<?php
declare(strict_types=1);

namespace App\Security;

use Nette\Application\Request;

/** Only include operation names and numeric record identifiers, never form data. */
final class ErrorContext
{
    public static function describe(int $status, ?Request $request): string
    {
        $params = $request ? $request->getParameters() : [];
        $post = $request ? $request->getPost() : [];
        $context = [
            'status' => $status,
            'presenter' => self::name($request ? $request->getPresenterName() : null),
            'action' => self::name($params['action'] ?? null),
            'signal' => self::name($post['_do'] ?? $params['do'] ?? null),
        ];
        foreach (['id', 'page_id'] as $key) {
            $value = $params[$key] ?? $post[$key] ?? null;
            if ((is_int($value) || is_string($value)) && preg_match('/^[0-9]{1,12}$/D', (string) $value)) {
                $context[$key] = (string) $value;
            }
        }
        return 'HTTP failure ' . json_encode($context, JSON_UNESCAPED_SLASHES);
    }

    private static function name($value): ?string
    {
        return is_string($value) && preg_match('/^[a-zA-Z0-9_:\\-]{1,100}$/D', $value) ? $value : null;
    }
}
