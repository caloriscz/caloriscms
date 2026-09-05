<?php

namespace Caloriscz\Utilities;

use elFinder;
use elFinderConnector;
use Nette\Application\UI\Control;

class ElfinderControl extends Control
{
    public function handleOptions(): void
    {
        $mediaPath = $this->getMediaPath();
        $mediaUrl = $mediaPath === '' ? '/media' : '/media/' . $mediaPath;
        $mediaDirectory = APP_DIR . '/media' . ($mediaPath === '' ? '' : '/' . $mediaPath);

        $opts = [
            'debug' => true,
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
