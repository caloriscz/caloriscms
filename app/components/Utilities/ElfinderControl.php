<?php

namespace Caloriscz\Utilities;

use App\Security\AdminPermissions;
use elFinder;
use elFinderConnector;
use Nette\Application\UI\Control;

class ElfinderControl extends Control
{
    public function handleOptions(): void
    {
        $presenter = $this->getPresenter();
        $role = $presenter->template->memberRole ? $presenter->template->memberRole->toArray() : [];
        $permissions = ['media', 'pictures'];
        if (!$presenter->getUser()->isLoggedIn()
            || (!AdminPermissions::allows($role, 'media') && !AdminPermissions::allows($role, 'pictures'))) {
            throw new \Nette\Application\ForbiddenRequestException('File manager access denied.');
        }

        $mediaPath = $this->getMediaPath();
        $mediaUrl = $mediaPath === '' ? '/media' : '/media/' . $mediaPath;
        $mediaDirectory = APP_DIR . '/media' . ($mediaPath === '' ? '' : '/' . $mediaPath);

        $opts = [
            'debug' => false,
            // Keep the Nette session open: elFinder's early close/reopen replaces
            // its cookie settings and can lose the login on local HTTP requests.
            'sessionCloseEarlier' => false,
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path' => $mediaDirectory,                 // path to files (REQUIRED)
                    'URL' => $mediaUrl, // URL to files (REQUIRED)
                    'uploadDeny' => ['all'],                // All Mimetypes not allowed to upload
                    'uploadAllow' => ['image', 'text/plain'],// Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder' => ['deny', 'allow'],      // allowed Mimetype `image` and `text/plain` only
                    'accessControl' => 'access',                     // disable and hide dot starting files (OPTIONAL)
                    'fileMode' => 0644,
                    'attributes' => $this->getHiddenDirectories()
                ],
                [
                    'driver' => 'LocalFileSystem',
                    'path' => APP_DIR . '/images',
                    'URL' => '/images',
                    'uploadDeny' => ['all'],
                    'uploadAllow' => ['image', 'text/plain'],
                    'uploadOrder' => ['deny', 'allow'],
                    'accessControl' => 'access',
                    'fileMode' => 0644,
                    'attributes' => $this->getHiddenDirectories()
                ]
            ]
        ];

        // Each root has its own permission; access to one must not expose the other.
        foreach ($permissions as $index => $permission) {
            if (!AdminPermissions::allows($role, $permission)) {
                unset($opts['roots'][$index]);
            }
        }
        $opts['roots'] = array_values($opts['roots']);

        // Run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }

    private function getMediaPath(): string
    {
        $path = isset($_GET['path']) ? trim((string) $_GET['path'], " \t\n\r\0\x0B/\\") : '';

        if ($path === '' || $path === 'null') {
            return '';
        }

        return preg_match('/^\d+$/', $path) ? $path : '';
    }

    /**
     * Array with hidden directories for Elfinder
     */
    public function getHiddenDirectories(): array
    {
        return [
            ['pattern' => '!^/tn!', 'hidden' => true],
            ['pattern' => '!^/\.tmb!', 'hidden' => true],
            ['pattern' => '!^/\.quarantine!', 'hidden' => true],
            ['pattern' => '!^/admin!', 'hidden' => true],
            ['pattern' => '!^/menu!', 'hidden' => true],
            ['pattern' => '!^/paths!', 'hidden' => true],
            ['pattern' => '!^/carousel!', 'hidden' => true],
            ['pattern' => '!^/profiles!', 'hidden' => true],
        ];

    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->render();
    }

}
