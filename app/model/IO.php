<?php

/*
 * Caloris
 * @copyright 2006-2015 Petr Karásek (http://caloris.cz)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use RecursiveDirectoryIterator,
    RecursiveIteratorIterator;
use Tracy\Debugger;

/**
 * File and directory handler
 * @author Petr Karásek
 */
class IO
{

    /**
     * Uploads file
     * @param $pathDirectory
     * @param $fileName
     * @param int $chmod
     * @return bool
     */
    public static function upload($pathDirectory, $fileName, $chmod = 0644): ?bool
    {
        $path = $pathDirectory . '/' . $fileName;

        if (file_exists($path)) {
            return false;
        } elseif ($_FILES['the_file']['tmp_name'] === '') {
            return false;
        } else {
            copy($_FILES['the_file']['tmp_name'], $path);
            chmod($path, $chmod);
            return true;
        }
    }

    /**
     * Remove file or empty directory
     * @param $fileName
     */
    public static function remove($fileName): void
    {
        if (file_exists($fileName)) {

            if (is_dir($fileName)) {
                rmdir($fileName);
            } else {
                unlink($fileName);
            }
        }
    }

    /**
     * Deletes directory with subdirectories and all files
     * @param $path
     */
    public static function removeDirectory($path, $leaveDir = null): void
    {
        $pathD = $path;
        if (file_exists($path)) {
            $filesModule = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);

            $filesModule->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
            foreach ($filesModule as $path) {
                if ($path->isDir()) {
                    self::removeDirectory($path->__toString());
                } else {
                    unlink($path->__toString());
                }
            }

            if ($leaveDir && file_exists($pathD)) {
                rmdir($pathD);
            }
        }
    }

    /**
     * Reads files.
     * @param $path
     * @return bool|string
     */
    public static function get($path)
    {
        $type = null;
        $fileHandle = fopen($path, 'r');

        if (file_exists($path) && filesize($path) > 0) {
            $type = fread($fileHandle, filesize($path));
        }

        fclose($fileHandle);

        return $type;
    }

    /**
     * Creates directory and sets user rights.
     * @param $path
     * @param int $chmod
     */
    public static function directoryMake($path, $chmod = 0755): void
    {
        $pathConvert = str_replace("\\", '/', $path);

        if (!file_exists($pathConvert)) {
            $oldUmask = umask(0);
            Debugger::barDump($pathConvert);
            mkdir($pathConvert, $chmod);
            umask($oldUmask);
        }
    }

    /**
     * Renames files or moves to a different directory.
     * @param $pathFrom
     * @param $pathTo
     */
    public static function rename($pathFrom, $pathTo): void
    {
        if (!file_exists($pathFrom) && file_exists($pathTo)) {
            rename($pathFrom, $pathTo);
        }
    }

    /**
     * Checks for empty directories
     * @param $directory
     * @return bool
     */
    public static function isEmptyDirectory($directory)
    {
        return (($files = @scandir($directory, SCANDIR_SORT_ASCENDING)) && \count($files) <= 2);
    }

    /**
     * Creates and/or edit file
     * @param $path
     * @param null $text
     * @param int $chmod
     */
    public static function file($path, $text = null, $chmod = 0755): void
    {
        $fileHandle = fopen($path, 'w');
        fwrite($fileHandle, $text);
        fclose($fileHandle);
        chmod($path, $chmod);
    }

    /**
     * Get files list as array
     * @param $filePath
     * @return array
     */
    public static function listFiles($filePath): array
    {
        $files = scandir($filePath, SCANDIR_SORT_ASCENDING);
        $arrayA = ['.', '..'];
        $filesComplete = array_diff($files, $arrayA);

        return array_values($filesComplete);
    }

    /**
     * Directory size
     * @param $dir
     * @return int
     */
    public static function folderSize($dir): int
    {
        $count_size = 0;
        $count = 0;
        $dir_array = scandir($dir, SCANDIR_SORT_ASCENDING);
        foreach ($dir_array as $key => $filename) {
            if ($filename !== '..' && $filename !== '.') {
                if (is_dir($dir . '/' . $filename)) {
                    $new_folderSize = folderSize($dir . '/' . $filename);
                    $count_size += $new_folderSize;
                } elseif (is_file($dir . '/' . $filename)) {
                    $count_size += filesize($dir . '/' . $filename);
                    $count++;
                }
            }
        }
        return $count_size;
    }

    /**
     * Is it image or other type of file
     * @param $path
     * @return bool
     */
    public static function isImage($path): bool
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $path);

        $valid_image_type = [];
        $valid_image_type['image/png'] = '';
        $valid_image_type['image/jpg'] = '';
        $valid_image_type['image/jpeg'] = '';
        $valid_image_type['image/gif'] = '';
        $valid_image_type['image/bmp'] = '';

        if (isset($valid_image_type[$type])) {
            return true;
        }

        return false;
    }

}
