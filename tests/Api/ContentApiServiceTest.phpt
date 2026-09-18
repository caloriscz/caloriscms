<?php
declare(strict_types=1);

use App\Model\Api\ContentApiService;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';

$db = createTestDatabase();
$service = new ContentApiService($db);

// This is the allowlist queried by the API presenter before calling the service.
foreach (['public', 'users_id', 'date_created', 'sorted', 'editable', 'unexpected'] as $field) {
    Assert::same($field, $service->findUnknownPageField([$field => 1]));
}
Assert::null($service->findUnknownPageField(['title' => 'Allowed', 'document' => '<p>Text</p>']));

foreach ([[], ['title' => ' '], ['title' => []], ['title' => str_repeat('a', 251)],
          ['title' => 'Bad relation', 'pages_types_id' => 999],
          ['title' => 'Bad boolean', 'sitemap' => 'yes'],
          ['title' => 'Bad date', 'date_published' => 'not-a-date']] as $invalid) {
    Assert::exception(function () use ($service, $invalid): void {
        $service->createPage($invalid, 1);
    }, InvalidArgumentException::class);
}
Assert::same(0, $db->table('pages')->count('*'));

$page = $service->createPage([
    'title' => ' Test Article ', 'pages_types_id' => 1, 'pages_templates_id' => 1,
    'document' => '<p>Keep this</p><script>alert(1)</script><img src="/safe.png" onerror="alert(2)">',
    'preview' => '<p onclick="alert(3)">Preview</p>',
], 1);
Assert::same('Test Article', $page['title']);
Assert::same('test-article', $page['slug']);
Assert::same(0, $page['public']);
Assert::same(1, $page['users_id']);
Assert::contains('<p>Keep this</p>', $page['document']);
Assert::notContains('<script', $page['document']);
Assert::notContains('onerror', $page['document']);
Assert::notContains('onclick', $page['preview']);

$duplicate = $service->createPage(['title' => 'Test Article'], 1);
Assert::notSame($page['slug'], $duplicate['slug']);
Assert::exception(function () use ($service, $page): void {
    $service->createPage(['title' => 'Duplicate', 'slug' => $page['slug']], 1);
}, InvalidArgumentException::class, 'Field slug must be unique');
Assert::exception(function () use ($service, $page): void {
    $service->updatePage($page['id'], ['pages_id' => $page['id']]);
}, InvalidArgumentException::class, 'Field pages_id cannot reference the same page');

$updated = $service->updatePage($page['id'], ['title' => 'Updated', 'sitemap' => false]);
Assert::same('Updated', $updated['title']);
Assert::same(0, $updated['sitemap']);
Assert::same($page['document'], $updated['document']);

$db->table('pages')->get($page['id'])->update(['public' => 1]);
Assert::true($service->unpublishPage($page['id']));
Assert::same(0, $service->getPage($page['id'])['public']);
Assert::same(2, $db->table('pages')->count('*')); // DELETE semantics keep the record.
Assert::false($service->unpublishPage(999));
Assert::null($service->updatePage(999, ['title' => 'Missing']));
