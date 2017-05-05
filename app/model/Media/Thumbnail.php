<?php

/*
 * Caloris
 * @copyright 2006-2015 Petr Karásek (http://caloris.cz)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Tracy\Debugger;


/**
 * Thumbnail maker
 * @author Petr Karásek
 */
class Thumbnail
{
    function setFile($path, $file)
    {
        $this->path = $path;
        $this->file = $file;
    }

    function getPath()
    {
        return $this->path;
    }

    function getFile()
    {
        return $this->file;
    }

    function setDimensions($width = null, $height = null)
    {
        $this->width = $width;
        $this->height = $height;
    }

    function getWidth()
    {
        if ($this->width == null && $this->height == null) {
            return 300;
        } else {
            return $this->width;
        }
    }

    function getHeight()
    {
        return $this->height;
    }

    /**
     * Check file and folder
     */
    function check($path)
    {
        \App\Model\IO::directoryMake(APP_DIR . "/" . $this->getPath() . $path, 0755);

        if (\App\Model\IO::isImage(APP_DIR . "/" . $this->getPath() . "/" . $this->getFile()) == false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Save to appropriate path
     */
    function save($path = "tn", $realFile = false)
    {
        if (!$this->check($path)) {
            return false;
        }

        if ($realFile) {
            $fileName = $realFile;
        } else {
            $fileName = $this->getFile();
        }

        $image = \Nette\Utils\Image::fromFile(APP_DIR . "/" . $this->getPath() . "/" . $this->getFile());
        $image->resize($this->getWidth(), $this->getHeight(), \Nette\Utils\Image::SHRINK_ONLY);
        $image->sharpen();
        $image->save(APP_DIR . "/" . $this->getPath() . "/" . $path . '/' . $fileName);
        chmod(APP_DIR . "/" . $this->getPath() . "/" . $path . '/' . $fileName, 0644);


        return true;
    }

}
