<?php
declare(strict_types=1);

use App\Security\AdminPermissions;
use Nette\Application\ForbiddenRequestException;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$full = ['sign' => 1, 'pages' => 1, 'pictures' => 1, 'media' => 1, 'members' => 1, 'settings' => 1,
    'menu' => 1, 'contacts' => 1, 'helpdesk' => 1, 'appearance' => 1];
$requests = [
    ['Admin:Pages', 'default', ['pageList', 'delete'], 'pages'],
    ['Admin:Pages', 'default', ['pageList', 'public'], 'pages'],
    ['Admin:Pages', 'detail', ['editor-editForm', 'submit'], 'pages'],
    ['Admin:Pages', 'default', ['imageBrowser', 'delete'], 'pictures'],
    ['Admin:Pages', 'default', ['imageBrowser', 'setMain'], 'pictures'],
    ['Admin:Pages', 'default', ['dropZonePictures-dropUploadForm', 'submit'], 'pictures'],
    ['Admin:Pages', 'detailImages', null, 'pictures'],
    ['Admin:Pages', 'default', ['productFileList', 'deleteFile'], 'media'],
    ['Admin:Pages', 'default', ['dropZoneMedia-dropForm', 'submit'], 'media'],
    ['Admin:Pages', 'detailFiles', null, 'media'],
    ['Admin:Files', 'default', ['', 'delete'], 'pictures'],
    ['Admin:Files', 'detailFile', ['', 'delete'], 'pictures'],
    ['Admin:Files', 'default', ['editFile-editForm', 'submit'], 'media'],
    ['Admin:Files', 'detailFile', ['dropUploadFiles-dropUploadForm', 'submit'], 'pictures'],
    ['Admin:Members', 'edit', ['sendLogin-sendLoginForm', 'submit'], 'members'],
    ['Admin:Members', 'edit', ['editMember-editForm', 'submit'], 'members'],
    ['Admin:Members', 'default', ['memberGrid', 'delete'], 'members'],
    ['Admin:Settings', 'blacklist', ['blackList', 'delete'], 'settings'],
    ['Admin:Homepage', 'default', ['editor-editForm', 'submit'], 'pages'],
];

$sections = [
    'Menu' => ['menu', ['menuEditor', 'menuEditor-menuInsert-insertForm', 'menuMenusEditForm-editForm', 'menuUpdateImages-updateImagesForm']],
    'Contacts' => ['contacts', ['contactGrid', 'editContact-editForm', 'insertHour-insertForm', 'categoryEdit-editForm']],
    'Links' => ['pages', ['categoryPanel', 'editForm', 'insertForm']],
    'Snippets' => ['pages', ['editSnippetForm-editSnippetForm', 'insertSnippetForm-insertForm']],
    'Helpdesk' => ['helpdesk', ['editHelpdeskEmailSettings-editForm', 'editMailTemplate-editForm']],
    'Appearance' => ['appearance', ['carouselManager', 'editFormCarousel-editForm', 'savePaths-editForm']],
];
foreach ($sections as $section => [$permission, $components]) {
    foreach (['default', 'detail', 'categories', 'carousel'] as $action) {
        $requests[] = ['Admin:' . $section, $action, null, $permission];
        foreach (array_merge([''], $components) as $component) {
            foreach (['submit', 'delete', 'rename', 'sort'] as $signal) {
                $requests[] = ['Admin:' . $section, $action, [$component, $signal], $permission];
            }
        }
    }
    // No unrelated grant is required, including settings for Helpdesk/Appearance.
    AdminPermissions::requireRequest(['sign' => 1, $permission => 1], 'Admin:' . $section, 'default', null);
    $missing = $full;
    unset($missing[$permission]);
    Assert::exception(function () use ($missing, $section): void {
        AdminPermissions::requireRequest($missing, 'Admin:' . $section, 'default', null);
    }, ForbiddenRequestException::class);
}

foreach ($requests as [$presenter, $action, $signal, $required]) {
    AdminPermissions::requireRequest($full, $presenter, $action, $signal);
    foreach ([[], array_merge($full, [$required => 0]), array_merge($full, ['sign' => 0])] as $denied) {
        Assert::exception(function () use ($denied, $presenter, $action, $signal): void {
            AdminPermissions::requireRequest($denied, $presenter, $action, $signal);
        }, ForbiddenRequestException::class);
    }
}

// Independent grants: page editors need no member privileges and profiles remain usable.
AdminPermissions::requireRequest(['sign' => '1', 'pages' => '1'], 'Admin:Pages', 'detail', null);
AdminPermissions::requireRequest(['sign' => 1], 'Admin:Profile', 'default', null);
Assert::false(AdminPermissions::allows(['sign' => 1], 'members'));
Assert::true(AdminPermissions::allows(['sign' => 1, 'media' => 1], 'media'));
Assert::false(AdminPermissions::allows(['sign' => 1, 'media' => 1], 'pictures'));
