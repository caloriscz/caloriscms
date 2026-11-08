<?php
declare(strict_types=1);

namespace App\Security;

use Nette\Application\ForbiddenRequestException;

final class AdminPermissions
{
    public static function allows(array $role, string $permission): bool
    {
        return (int) ($role['sign'] ?? 0) === 1
            && (int) ($role[$permission] ?? 0) === 1;
    }

    public static function requirePermission(array $role, string $permission): void
    {
        if (!self::allows($role, $permission)) {
            throw new ForbiddenRequestException('You do not have permission for this action.');
        }
    }

    /** Check the resolved Nette action/signal, including POST _do and AJAX signals. */
    public static function requireRequest(array $role, string $presenter, string $action, ?array $signal): void
    {
        $presenter = strtolower($presenter);
        $action = strtolower($action);
        $component = strtolower(explode('-', $signal[0] ?? '')[0]);

        if ($presenter === 'admin:pages') {
            self::requirePermission($role, 'pages');
            if (in_array($action, ['detailimages', 'imagesdetail'], true)
                || in_array($component, ['imagebrowser', 'imageeditform', 'dropzonepictures'], true)) {
                self::requirePermission($role, 'pictures');
            }
            if ($action === 'detailfiles'
                || in_array($component, ['productfilelist', 'dropzonemedia'], true)) {
                self::requirePermission($role, 'media');
            }
        } elseif ($presenter === 'admin:files') {
            self::requirePermission($role, $action === 'detailfile' ? 'media' : 'pictures');
            // Component signals can be submitted on an action other than their form page.
            if ($component === 'editfile') {
                self::requirePermission($role, 'media');
            } elseif (in_array($component, ['editpicture', 'dropuploadfiles'], true)
                || strtolower($signal[1] ?? '') === 'delete') {
                self::requirePermission($role, 'pictures');
            }
        } elseif ($presenter === 'admin:members') {
            self::requirePermission($role, 'members');
        } elseif ($presenter === 'admin:settings') {
            self::requirePermission($role, 'settings');
        }

        // These sections share one grant across all actions and nested components.
        // Links and snippets are content; the legacy schema has no separate grants.
        $sections = [
            'admin:menu' => 'menu',
            'admin:contacts' => 'contacts',
            'admin:links' => 'pages',
            'admin:snippets' => 'pages',
            'admin:helpdesk' => 'helpdesk',
            'admin:appearance' => 'appearance',
        ];
        if (isset($sections[$presenter])) {
            self::requirePermission($role, $sections[$presenter]);
        }

        // The shared base presenter exposes this editor on every admin presenter.
        if ($component === 'editor') {
            self::requirePermission($role, 'pages');
        }
    }
}
