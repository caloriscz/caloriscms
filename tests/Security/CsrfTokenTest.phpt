<?php
declare(strict_types=1);

use App\Security\CsrfToken;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$token = CsrfToken::generate();

Assert::same(64, strlen($token));
Assert::match('#^[a-f0-9]{64}$#', $token);
Assert::notSame($token, CsrfToken::generate());

Assert::true(CsrfToken::validatesRequest('POST', $token, $token));
Assert::true(CsrfToken::validatesRequest('post', $token, $token));
Assert::false(CsrfToken::validatesRequest('GET', $token, $token));
Assert::false(CsrfToken::validatesRequest('POST', null, $token));
Assert::false(CsrfToken::validatesRequest('POST', '', $token));
Assert::false(CsrfToken::validatesRequest('POST', str_repeat('0', 64), $token));

foreach (['HEAD', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] as $method) {
    Assert::false(CsrfToken::validatesRequest($method, $token, $token));
}

// PHP can parse attacker-controlled form input as an array rather than a string.
foreach ([[$token], true, 123, new stdClass(), substr($token, 1), $token . '0'] as $submitted) {
    Assert::false(CsrfToken::validatesRequest('POST', $submitted, $token));
}
Assert::false(CsrfToken::validatesRequest('POST', CsrfToken::generate(), $token));
