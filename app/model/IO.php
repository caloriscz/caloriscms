<?php

/*
 * Caloris
 * @copyright 2006-2015 Petr Karásek (http://caloris.cz)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use RecursiveDirectoryIterator,
    RecursiveIteratorIterator;

/**
 * File and directory handler
 * @author Petr Karásek
 */
class IO
{

    /**
     *  Uploads file
     */
    public static function upload($pathDirectory, $fileName, $chmod = 0777)
    {
        $path = $pathDirectory . '/' . $fileName;

        if (file_exists($path)) {
            return false;
        } elseif ($_FILES['the_file']['tmp_name'] == "") {
            return false;
        } else {
            copy($_FILES["the_file"]["tmp_name"], $path);
            chmod($path, $chmod);
            return true;
        }
    }

    /**
     *  Remove file or empty directory
     */
    public static function remove($fileName)
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
     *  Deletes directory with subdirectories and all files
     */
    public static function removeDirectory($path)
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

            if (file_exists($pathD)) {
                rmdir($pathD);
            }
        }
    }

    /**
     *  Reads files. When 1 is set to highlight parameter, Caloris extensions syntax is highlighted
     *  @param string $path Path to file
     */
    public static function get($path)
    {
        $fileHandle = fopen($path, "r");

        if (file_exists($path) && filesize($path) > 0) {
            $typer = fread($fileHandle, filesize($path));
        }

        fclose($fileHandle);

        return $typer;
    }

    /**
     *  Creates directory and sets user rights.
     */
    public static function directoryMake($path, $chmod = 0755)
    {
        if (!file_exists($path)) {
            $oldUmask = umask(0);
            mkdir($path, $chmod);
            chmod($path, $chmod);
            umask($oldUmask);
        }
    }

    /**
     *  Renames files or moves to a different directory.
     */
    public static function rename($pathFrom, $pathTo)
    {
        if (!file_exists($pathFrom) && file_exists($pathTo)) {
            rename($pathFrom, $pathTo);
        }
    }

    /**
     *  Checks for empty diorectories
     */
    public static function isEmptyDirectory($directory)
    {
        return (($files = @scandir($directory)) && count($files) <= 2);
    }

    /**
     * Creates and/or edit file
     */
    public static function file($path, $text = null, $chmod = 0777)
    {
        $fileHandle = fopen($path, 'w');
        fwrite($fileHandle, $text);
        fclose($fileHandle);
        chmod($path, $chmod);
    }

    /**
     * Get files list as array
     */
    public static function listFiles($filePath)
    {
        $files = scandir($filePath);
        $arrayA = array(".", "..");
        $filesComplete = array_diff($files, $arrayA);

        return array_values($filesComplete);
    }

    /*
     * Folder size
     */

    public static function folderSize($dir)
    {
        $count_size = 0;
        $count = 0;
        $dir_array = scandir($dir);
        foreach ($dir_array as $key => $filename) {
            if ($filename != ".." && $filename != ".") {
                if (is_dir($dir . "/" . $filename)) {
                    $new_foldersize = foldersize($dir . "/" . $filename);
                    $count_size = $count_size + $new_foldersize;
                } else if (is_file($dir . "/" . $filename)) {
                    $count_size = $count_size + filesize($dir . "/" . $filename);
                    $count++;
                }
            }
        }
        return $count_size;
    }

}
