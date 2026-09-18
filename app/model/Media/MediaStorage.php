<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Application\BadRequestException;
use Nette\Database\Explorer;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use RuntimeException;
use Throwable;
use Tracy\Debugger;

/** Coordinates page-owned files with database writes; never repairs existing data. */
class MediaStorage
{
    private Explorer $database;
    private string $root;

    public function __construct(Explorer $database, string $root)
    {
        $this->database = $database;
        $resolved = realpath($root);
        if ($resolved === false) {
            throw new RuntimeException('Media document root does not exist.');
        }
        $this->root = str_replace('\\', '/', $resolved);
    }

    public function upload(string $table, $pageId, array $file, int $type = 0, array $settings = []): int
    {
        if (!is_scalar($pageId) || !preg_match('/^[1-9][0-9]*$/D', (string) $pageId)) {
            throw new BadRequestException('Invalid media page.');
        }
        $name = IO::sanitizeUploadFileName($file['name'] ?? '');
        $tmp = $file['tmp_name'] ?? '';
        if ($name === null || strlen($name) > 140 || !is_string($tmp)
            || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($tmp)
            || (($table === 'pictures' || $type === 1) && !IO::isAllowedImageUpload($name, $tmp))) {
            throw new BadRequestException('Invalid media upload.');
        }
        $thumbnail = null;
        if ($table === 'pictures') {
            $thumbnail = static function (string $source, string $target) use ($settings): void {
                $image = Image::fromFile($source);
                $image->resize((int) ($settings['media_thumb_width'] ?? 300),
                    (int) ($settings['media_thumb_height'] ?? 300), Image::SHRINK_ONLY);
                $image->sharpen();
                $image->save($target);
            };
        }
        return $this->store($table, (int) $pageId, $name, static function (string $target) use ($tmp): void {
            if (!move_uploaded_file($tmp, $target)) {
                throw new RuntimeException('Could not stage uploaded file.');
            }
        }, $thumbnail, $type, (string) ($settings['media_thumb_dir'] ?? 'tn'));
    }

    /** Writer callbacks are internal only; they must write the supplied staging path. */
    public function store(string $table, int $pageId, string $name, callable $write,
        ?callable $thumbnail = null, int $type = 0, string $thumbDir = 'tn'): int
    {
        $directory = $this->directory($table, $pageId);
        $this->name($name);
        $this->name($thumbDir);
        $stage = $this->stage();
        $placed = [];
        try {
            $id = $this->transaction(function () use ($table, $pageId, $name, $write, $thumbnail,
                $type, $thumbDir, $directory, $stage, &$placed): int {
                $this->lockPage($pageId);
                if ($this->database->table($table)->where(['pages_id' => $pageId, 'name' => $name])->count('*')
                    || file_exists($directory . '/' . $name)) {
                    throw new BadRequestException('A file with this name already exists.', 409);
                }
                $source = $stage . '/' . $name;
                $write($source);
                if (!is_file($source) || is_link($source)) {
                    throw new RuntimeException('Upload did not produce a regular file.');
                }
                if ($thumbnail) {
                    FileSystem::createDir($stage . '/thumb');
                    $thumbnail($source, $stage . '/thumb/' . $name);
                    if (!is_file($stage . '/thumb/' . $name)) {
                        throw new RuntimeException('Thumbnail was not created.');
                    }
                }
                FileSystem::createDir($directory);
                $targets = [$source => $directory . '/' . $name];
                if ($thumbnail) {
                    $this->safePath($directory . '/' . $thumbDir);
                    FileSystem::createDir($directory . '/' . $thumbDir);
                    $targets[$stage . '/thumb/' . $name] = $directory . '/' . $thumbDir . '/' . $name;
                }
                foreach ($targets as $from => $to) {
                    $this->safePath($to);
                    if (file_exists($to)) {
                        throw new BadRequestException('A file with this name already exists.', 409);
                    }
                    FileSystem::rename($from, $to, false);
                    $placed[] = $to;
                    FileSystem::write($stage . '/placed.json', json_encode($placed));
                    if (!chmod($to, 0644)) {
                        throw new RuntimeException('Could not set media permissions.');
                    }
                }
                $row = $this->database->table($table)->insert([
                    'pages_id' => $pageId, 'name' => $name, 'filesize' => filesize($directory . '/' . $name),
                    'file_type' => $type, 'date_created' => date('Y-m-d H:i:s'), 'description' => '', 'sorted' => 0,
                ]);
                return (int) $row->id;
            }, function () use (&$placed): void {
                foreach (array_reverse($placed) as $path) {
                    FileSystem::delete($path);
                }
                $placed = [];
            });
        } catch (Throwable $error) {
            // Keep a failed rollback's journal/files for diagnosis.
            if ($placed === []) { $this->purge($stage); }
            throw $error;
        }
        $this->purge($stage);
        return $id;
    }

    public function deleteFile(string $table, int $id, string $thumbDir = 'tn'): ?int
    {
        $this->table($table);
        $this->name($thumbDir);
        $row = $this->database->table($table)->get($id);
        if (!$row) {
            return null;
        }
        $pageId = (int) $row->pages_id;
        $this->deletion($pageId, function (callable $stage) use ($table, $id, $pageId, $thumbDir): void {
            $row = $this->database->table($table)->get($id);
            if (!$row || (int) $row->pages_id !== $pageId) {
                throw new RuntimeException('Media changed during deletion.');
            }
            $this->name($row->name);
            $path = $this->directory($table, $pageId);
            $stage($path . '/' . $row->name);
            if ($table === 'pictures') {
                foreach (array_unique(['tn', $thumbDir]) as $dir) {
                    $stage($path . '/' . $dir . '/' . $row->name);
                }
            }
            $row->delete();
        });
        return $pageId;
    }

    public function deletePage(int $id): bool
    {
        if (!$this->database->table('pages')->get($id)) {
            return false;
        }
        $this->deletion($id, function (callable $stage) use ($id): void {
            foreach (['media', 'pictures'] as $table) {
                $stage($this->directory($table, $id));
                // pictures has no foreign key in the legacy schema.
                $this->database->table($table)->where('pages_id', $id)->delete();
            }
            $this->database->table('pages')->where('id', $id)->delete();
        });
        return true;
    }

    private function deletion(int $pageId, callable $delete): void
    {
        $stage = $this->stage();
        $moved = [];
        try {
            $this->transaction(function () use ($pageId, $delete, $stage, &$moved): void {
                $this->lockPage($pageId);
                $delete(function (string $path) use ($stage, &$moved): void {
                    $this->safePath($path);
                    if (!file_exists($path)) {
                        return; // Missing files must not prevent deleting their stale reference.
                    }
                    if (is_dir($path)) {
                        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $entry) {
                            if ($entry->isLink()) { throw new RuntimeException('Symbolic links are not supported in media deletion.'); }
                        }
                    }
                    $destination = $stage . '/' . count($moved);
                    // Journal before rename, for read-only diagnosis after a process crash.
                    FileSystem::write($stage . '/restore.json', json_encode($moved + [$destination => $path]));
                    FileSystem::rename($path, $destination, false);
                    $moved[$destination] = $path;
                });
            }, function () use (&$moved): void {
                foreach (array_reverse($moved, true) as $from => $to) {
                    FileSystem::rename($from, $to, false);
                }
                $moved = [];
            });
        } catch (Throwable $error) {
            if ($moved === []) { $this->purge($stage); }
            throw $error;
        }
        $this->purge($stage);
    }

    private function transaction(callable $work, callable $undo)
    {
        return $this->database->transaction(static function () use ($work, $undo) {
            try {
                return $work();
            } catch (Throwable $error) {
                // Restore files while the database still holds the page lock.
                try { $undo(); } catch (Throwable $restore) {
                    Debugger::log($restore, Debugger::ERROR);
                    throw new RuntimeException('Media rollback needs manual recovery; inspect media:audit.', 0, $error);
                }
                throw $error;
            }
        });
    }

    private function purge(string $stage): void
    {
        try {
            FileSystem::delete($stage);
        } catch (Throwable $error) {
            // DB already committed; retained staging is diagnostic, never restore it blindly.
            Debugger::log(new RuntimeException('Media cleanup pending: ' . basename($stage), 0, $error), Debugger::ERROR);
        }
    }

    private function lockPage(int $id): void
    {
        $suffix = $this->database->getConnection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql'
            ? ' FOR UPDATE' : '';
        if (!$this->database->query('SELECT id FROM pages WHERE id = ?' . $suffix, $id)->fetch()) {
            throw new BadRequestException('Media page not found.', 404);
        }
    }

    private function stage(): string
    {
        $path = $this->root . '/temp/media-operations/' . bin2hex(random_bytes(16));
        $this->safePath($path);
        FileSystem::createDir($path);
        return $path;
    }

    private function table(string $table): void
    {
        if (!in_array($table, ['media', 'pictures'], true)) {
            throw new \InvalidArgumentException('Unknown media table.');
        }
    }

    private function name(string $name): void
    {
        if ($name === '' || $name === '.' || $name === '..' || preg_match('~[/\\\\\x00-\x1f]~', $name)) {
            throw new \InvalidArgumentException('Invalid media path component.');
        }
    }

    private function directory(string $table, int $pageId): string
    {
        $this->table($table);
        if ($pageId < 1) {
            throw new \InvalidArgumentException('Media must belong to a page.');
        }
        $path = $this->root . '/' . $table . '/' . $pageId;
        $this->safePath($path);
        return $path;
    }

    private function safePath(string $path): void
    {
        if (strpos($path, $this->root . '/') !== 0) {
            throw new RuntimeException('Media path is outside the document root.');
        }
        $part = $this->root;
        foreach (explode('/', substr($path, strlen($this->root) + 1)) as $name) {
            $this->name($name);
            $part .= '/' . $name;
            if (is_link($part)) {
                throw new RuntimeException('Symbolic links are not supported in media operations.');
            }
        }
    }
}
