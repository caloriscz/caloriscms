<?php
declare(strict_types=1);

use App\Model\Api\ApiTokenAuthenticator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';

$db = createTestDatabase();
$auth = new ApiTokenAuthenticator($db);
Assert::same('ba7816bf8f01cfea414140de5dae2223b00361a396177a9cb410ff61f20015ad', $auth->hashToken('abc'));

foreach ([null, '', 'Basic abc', 'Bearer ', 'Bearer unknown'] as $header) {
    Assert::null($auth->authenticate($header));
}

$db->table('api_tokens')->insert([
    'id' => 1, 'users_id' => 1, 'token_hash' => $auth->hashToken('test-secret'),
    'scopes' => ' pages:read, ,media:read ',
]);
$identity = $auth->authenticate('bEaReR test-secret');
Assert::notNull($identity);
Assert::same(1, (int) $identity->user->id);
Assert::same(['pages:read', 'media:read'], $identity->getScopes());
Assert::true($identity->hasScope('pages:read'));
Assert::false($identity->hasScope('pages:write'));
Assert::notNull($db->table('api_tokens')->get(1)->last_used_at);

// Invalid credentials never update usage, including expired/revoked/disabled cases.
foreach ([
    ['revoked_at' => '2020-01-01 00:00:00'],
    ['expires_at' => '2000-01-01 00:00:00'],
    ['users_id' => 2],
    ['users_id' => null],
] as $invalid) {
    $db->table('api_tokens')->get(1)->update(array_merge([
        'users_id' => 1, 'revoked_at' => null, 'expires_at' => null, 'last_used_at' => null,
    ], $invalid));
    Assert::null($auth->authenticate('Bearer test-secret'));
    Assert::null($db->table('api_tokens')->get(1)->last_used_at);
}

$db->table('api_tokens')->get(1)->update([
    'users_id' => 1, 'expires_at' => '2999-01-01 00:00:00', 'scopes' => '*',
]);
Assert::true($auth->authenticate('Bearer test-secret')->hasScope('pages:write'));
$db->table('users')->get(1)->update(['state' => 0]);
Assert::null($auth->authenticate('Bearer test-secret'));
