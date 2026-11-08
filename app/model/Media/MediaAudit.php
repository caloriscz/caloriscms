<?php
declare(strict_types=1);
namespace App\Model;

use Nette\Database\Explorer;

/** Read-only inventory of page-owned media; unrelated asset roots are excluded. */
final class MediaAudit
{
    public static function inspect(Explorer $db, string $root, string $thumbDir = 'tn'): array
    {
        $result = ['missing_originals' => [], 'missing_thumbnails' => [], 'orphan_rows' => [],
            'unreferenced_files' => [], 'unsafe_paths' => [], 'pending_operations' => []];
        $pages = $db->table('pages')->fetchPairs('id', 'id');
        foreach (['media', 'pictures'] as $table) {
            $expected = [];
            foreach ($db->table($table) as $row) {
                $key = $table . ':' . $row->id;
                if (!$row->pages_id || !isset($pages[$row->pages_id])) {
                    $result['orphan_rows'][] = $key;
                }
                if (!$row->pages_id || !preg_match('/^[1-9][0-9]*$/D', (string) $row->pages_id)
                    || !self::safeName($row->name) || !self::safeName($thumbDir)) {
                    $result['unsafe_paths'][] = $key;
                    continue;
                }
                $paths = [$row->pages_id . '/' . $row->name => 'missing_originals'];
                if ($table === 'pictures') {
                    $paths[$row->pages_id . '/' . $thumbDir . '/' . $row->name] = 'missing_thumbnails';
                }
                foreach ($paths as $relative => $category) {
                    $expected[$relative] = true;
                    $path = $root . '/' . $table . '/' . $relative;
                    if (self::hasLink($root, $table . '/' . $relative)) {
                        $result['unsafe_paths'][] = $key;
                    } elseif (!is_file($path)) {
                        $result[$category][] = $key;
                    }
                }
            }
            $directory = $root . '/' . $table;
            if (!is_dir($directory) || is_link($directory)) { continue; }
            foreach (new \DirectoryIterator($directory) as $page) {
                if ($page->isDot() || !ctype_digit($page->getFilename())) { continue; }
                if ($page->isLink()) { $result['unsafe_paths'][] = $table . '/' . $page->getFilename(); continue; }
                if (!$page->isDir()) { continue; }
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($page->getPathname(), \FilesystemIterator::SKIP_DOTS));
                foreach ($files as $file) {
                    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($directory) + 1));
                    if ($file->isLink()) {
                        $result['unsafe_paths'][] = $table . '/' . $relative;
                    } elseif ($file->isFile() && !isset($expected[$relative])) {
                        $result['unreferenced_files'][] = $table . '/' . $relative;
                    }
                }
            }
        }
        foreach (glob($root . '/temp/media-operations/*', GLOB_ONLYDIR) ?: [] as $path) {
            $result['pending_operations'][] = basename($path);
        }
        return $result;
    }

    private static function safeName(string $name): bool
    {
        return $name !== '' && $name !== '.' && $name !== '..' && !preg_match('~[/\\\\\x00-\x1f]~', $name);
    }

    private static function hasLink(string $root, string $relative): bool
    {
        foreach (explode('/', $relative) as $part) {
            $root .= '/' . $part;
            if (is_link($root)) { return true; }
        }
        return false;
    }
}
