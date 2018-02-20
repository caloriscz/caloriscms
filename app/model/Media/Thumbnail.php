<?php

/*
 * Caloris
 * @copyright 2006-2015 Petr Karásek (http://caloris.cz)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Nette\Utils\Image;

/**
 * Thumbnail maker
 * @author Petr Karásek
 */
class Thumbnail
{
    private $height;
    private $width;
    private $file;
    private $path;

    public function setFile($path, $file): void
    {
        $this->path = $path;
        $this->file = $file;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setDimensions($width = null, $height = null): void
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth(): ?int
    {
        if ($this->width === null && $this->height === null) {
            return 300;
        }

        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Check file and folder
     * @param $path
     * @return bool
     */
    public function check($path): ?bool
    {
        IO::directoryMake(APP_DIR . '/' . $this->getPath() . '/' . $path);

        if (IO::isImage(APP_DIR . '/' . $this->getPath() . '/' . $this->getFile()) === false) {
            return false;
        }

        return true;
    }

    /**
     * Save to appropriate path
     * @param string $path
     * @param bool $realFile
     * @return bool
     * @throws \Nette\Utils\UnknownImageFileException
     */
    public function save($path = 'tn', $realFile = false): bool
    {
        if (!$this->check($path)) {
            return false;
        }

        if ($realFile) {
            $fileName = $realFile;
        } else {
            $fileName = $this->getFile();
        }

        $image = Image::fromFile(APP_DIR . '/' . $this->getPath() . '/' . $this->getFile());
        $image->resize($this->getWidth(), $this->getHeight(), Image::SHRINK_ONLY);
        $image->sharpen();
        $image->save(APP_DIR . '/' . $this->getPath() . '/' . $path . '/' . $fileName);
        chmod(APP_DIR . '/' . $this->getPath() . '/' . $path . '/' . $fileName, 0644);

        return true;
    }
}
