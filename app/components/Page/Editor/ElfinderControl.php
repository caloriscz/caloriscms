<?php
namespace Caloriscz\Page\Editor;

use Nette\Application\UI\Control;

class ElfinderControl extends Control
{
    public function handleOptions()
    {
        $opts = array(
            'debug' => true,
            'roots' => array(
                array(
                    'driver' => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path' => APP_DIR . '/media/' . $_GET["path"],                 // path to files (REQUIRED)
                    'URL' => '/www/media/' . $_GET["path"], // URL to files (REQUIRED)
                    'uploadDeny' => array('all'),                // All Mimetypes not allowed to upload
                    'uploadAllow' => array('image', 'text/plain'),// Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder' => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                    'accessControl' => 'access',                     // disable and hide dot starting files (OPTIONAL)
                    'fileMode' => 0644,
                    'attributes' => $this->getHiddenDirectories()
                ),
                array(
                    'driver' => 'LocalFileSystem',
                    'path' => APP_DIR . '/images',
                    'URL' => '/www/images',
                    'uploadDeny' => array('all'),
                    'uploadAllow' => array('image', 'text/plain'),
                    'uploadOrder' => array('deny', 'allow'),
                    'accessControl' => 'access',
                    'fileMode' => 0644,
                    'attributes' => $this->getHiddenDirectories()
                )
            )
        );

        // Run elFinder
        $connector = new \elFinderConnector(new \elFinder($opts));
        $connector->run();
    }

    /**
     * Array with hidden directories for Elfinder
     */
    function getHiddenDirectories()
    {
        return array(
            array('pattern' => '!^/tn!', 'hidden' => true),
            array('pattern' => '!^/\.tmb!', 'hidden' => true),
            array('pattern' => '!^/\.quarantine!', 'hidden' => true),
            array('pattern' => '!^/admin!', 'hidden' => true),
            array('pattern' => '!^/menu!', 'hidden' => true),
            array('pattern' => '!^/paths!', 'hidden' => true),
            array('pattern' => '!^/carousel!', 'hidden' => true),
            array('pattern' => '!^/profiles!', 'hidden' => true),
        );

    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->render();
    }

}