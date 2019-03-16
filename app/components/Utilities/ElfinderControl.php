<?php

namespace Caloriscz\Utilities;

use elFinder;
use elFinderConnector;
use Nette\Application\UI\Control;

class ElfinderControl extends Control
{
    public function handleOptions(): void
    {
        $opts = [
            'debug' => true,
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path' => APP_DIR . '/media/' . $_GET['path'],                 // path to files (REQUIRED)
                    'URL' => '/www/media/' . $_GET['path'], // URL to files (REQUIRED)
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
                    'URL' => '/www/images',
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