<?php
declare(strict_types=1);
namespace App\Model;

use Nette\Application\BadRequestException;
use Nette\Database\Explorer;
use Nette\Utils\FileSystem;
use RuntimeException;
use Throwable;
use Tracy\Debugger;

/** Caught-failure rollback for non-page assets; caller owns authorization. */
class AssetStorage
{
    /** User-facing messages; callers show BadRequestException messages as flash or JSON errors. */
    public const INVALID_IMAGE = 'Neplatný obrázek. Nahrajte soubor PNG, JPG, GIF nebo BMP s názvem do 140 znaků.';
    public const DUPLICATE = 'Soubor s tímto názvem už existuje. Přejmenujte ho, nebo nejdříve smažte původní.';

    private Explorer $database;
    private string $root;
    private ?string $stage = null;
    private array $undo = [];

    public function __construct(Explorer $database, string $root)
    {
        $this->database = $database;
        $resolved = realpath($root);
        if ($resolved === false) { throw new RuntimeException('Asset root missing.'); }
        $this->root = str_replace('\\', '/', $resolved);
    }

    /** Shared with the connector; process exit releases the advisory lock. */
    public static function lock(string $root)
    {
        foreach (['/temp', '/temp/asset-operations', '/temp/asset-operations/lock'] as $part) {
            if (is_link($root . $part)) { throw new RuntimeException('Unsafe asset staging path.'); }
        }
        FileSystem::createDir($root . '/temp/asset-operations');
        $lock = fopen($root . '/temp/asset-operations/lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX)) { throw new RuntimeException('Asset lock unavailable.'); }
        return $lock;
    }

    public function run(callable $work)
    {
        $lock = self::lock($this->root);
        $this->stage = $this->root . '/temp/asset-operations/' . bin2hex(random_bytes(12));
        $this->undo = [];
        FileSystem::createDir($this->stage);
        $committing = false;
        try {
            $result = $this->database->transaction(function () use ($work, &$committing) {
                try { $result = $work($this); }
                catch (Throwable $error) {
                    try {
                        foreach (array_reverse($this->undo) as $entry) {
                            if ($entry['backup'] === null) { FileSystem::delete($entry['target']); }
                            else { FileSystem::rename($entry['backup'], $entry['target'], false); }
                        }
                        $this->undo = [];
                    } catch (Throwable $restore) {
                        throw new RuntimeException('Asset restoration requires manual recovery.', 0, $error);
                    }
                    throw $error;
                }
                $committing = true;
                return $result;
            });
            $this->purge();
            return $result;
        } catch (Throwable $error) {
            // Commit failures are ambiguous: retain files/journal for investigation.
            if (!$committing && !$this->undo) { $this->purge(); }
            if (!$error instanceof BadRequestException) {
                Debugger::log('Asset operation failed; stage=' . basename($this->stage)
                    . '; phase=' . ($committing ? 'commit' : 'write') . '; category=' . get_class($error), Debugger::ERROR);
            }
            throw $error;
        } finally {
            $this->stage = null;
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public static function hasUpload(array $file): bool
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) { return false; }
        if ($error !== UPLOAD_ERR_OK) { throw new BadRequestException('Obrázek se nepodařilo nahrát (je příliš velký nebo se přenos přerušil).', 400); }
        return true;
    }

    public static function imageName(array $file): string
    {
        $name = IO::sanitizeUploadFileName($file['name'] ?? '');
        if (!self::hasUpload($file) || $name === null || strlen($name) > 140
            || !IO::isAllowedImageUpload($name, $file['tmp_name'] ?? '')) {
            throw new BadRequestException(self::INVALID_IMAGE, 400);
        }
        return $name;
    }

    public function image(array $file, string $relative, bool $replace = false): void
    {
        self::imageName($file);
        $this->put($relative, static function (string $path) use ($file): void {
            if (!move_uploaded_file($file['tmp_name'], $path)) { throw new RuntimeException('Image staging failed.'); }
        }, $replace);
    }

    /** Internal writer seam, also used to inject failed writes in tests. */
    public function put(string $relative, callable $writer, bool $replace = false): void
    {
        $target = $this->path($relative);
        if (file_exists($target) && !$replace) { throw new BadRequestException(self::DUPLICATE, 409); }
        $source = $this->stage . '/new-' . bin2hex(random_bytes(8));
        $writer($source);
        if (!is_file($source) || is_link($source) || !chmod($source, 0644)) { throw new RuntimeException('Asset staging failed.'); }
        FileSystem::createDir(dirname($target));
        if ($replace) { $this->remove($relative); }
        $this->writeJournal(array_merge($this->undo, [['target' => $target, 'backup' => null]]));
        FileSystem::rename($source, $target, false);
        $this->undo[] = ['target' => $target, 'backup' => null];
    }

    public function remove(string $relative): void
    {
        $target = $this->path($relative, true);
        if (!file_exists($target)) { return; }
        $backup = $this->stage . '/old-' . count($this->undo);
        // Record the intent before moving; add undo only once rename succeeds.
        $this->writeJournal(array_merge($this->undo, [['target' => $target, 'backup' => $backup]]));
        FileSystem::rename($target, $backup, false);
        $this->undo[] = ['target' => $target, 'backup' => $backup];
    }

    /**
     * Whether an existing stored name can be removed from a fixed directory.
     * Rows from before upload names were sanitized hold raw names with spaces
     * or diacritics; any plain basename qualifies. No separators, NUL or other
     * control characters, and no leading dot ('.', '..', .htaccess, .tmb).
     */
    public static function isRemovableName(string $name): bool
    {
        return $name !== '' && $name[0] !== '.' && strlen($name) <= 255
            && !preg_match('~[/\\\\\x00-\x1F\x7F]~', $name);
    }

    private function path(string $relative, bool $existing = false): string
    {
        if ($this->stage === null) { throw new RuntimeException('Asset operation is not active.'); }
        if (!preg_match('~^(images/(?:menu/|carousel/|paths/)?|links-media/)(.+)$~sD', $relative, $match)
            || !($existing ? self::isRemovableName($match[2])
                : preg_match('~^[A-Za-z0-9][A-Za-z0-9_.-]*$~D', $match[2]) && strpos($match[2], '..') === false)) {
            throw new BadRequestException('Neplatný název souboru.', 400);
        }
        $path = $this->root;
        foreach (explode('/', $relative) as $part) {
            $path .= '/' . $part;
            if (is_link($path)) { throw new BadRequestException('Symbolické odkazy nejsou podporovány.', 400); }
            if (file_exists($path) && $path !== $this->root . '/' . $relative && !is_dir($path)) {
                throw new RuntimeException('Asset parent is not a directory.');
            }
        }
        if (file_exists($path) && !is_file($path)) { throw new BadRequestException('Cesta nevede na soubor.', 400); }
        return $path;
    }

    private function writeJournal(array $entries): void
    {
        FileSystem::write($this->stage . '/restore.json', json_encode($entries));
    }

    private function purge(): void
    {
        try { FileSystem::delete($this->stage); }
        catch (Throwable $error) { Debugger::log('Asset cleanup pending; stage=' . basename($this->stage), Debugger::ERROR); }
    }
}
