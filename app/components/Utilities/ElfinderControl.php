<?php

namespace Caloriscz\Utilities;

use App\Security\AdminPermissions;
use elFinder;
use elFinderConnector;
use Nette\Application\UI\Control;

class ElfinderControl extends Control
{
    /**
     * elFinder's default without SVG: its default serves an SVG without an XML
     * declaration inline as "image/svg", and whether a browser then runs its
     * scripts differs by browser. Direct /images/*.svg is already denied.
     */
    private const DISP_INLINE = '^(?:(?:video|audio)|image/(?!svg|.+\+xml)|application/(?:ogg|x-mpegURL|dash\+xml)|(?:text/plain|application/pdf)$)';

    /**
     * elFinder's Linux default for the local driver (no leading dot, no '/') without
     * control characters, which have no use in a file name. A newline in a folder
     * name took a dot file inside it out of the rule in getHiddenDirectories().
     */
    public const ACCEPTED_NAME = '/^[^.\/\p{Cc}][^\/\p{Cc}]*$/Du';

    /** What elFinder's tempnam() call names a prepared ZIP. */
    private const PREPARED_ZIP = '/^elfzdl-[a-zA-Z0-9]+$/D';

    public function handleOptions(): void
    {
        $presenter = $this->getPresenter();
        $role = $presenter->template->memberRole ? $presenter->template->memberRole->toArray() : [];
        $permissions = ['media', 'pictures'];
        if (!$presenter->getUser()->isLoggedIn()
            || (!AdminPermissions::allows($role, 'media') && !AdminPermissions::allows($role, 'pictures'))) {
            throw new \Nette\Application\ForbiddenRequestException('File manager access denied.');
        }

        $mediaPath = $this->getMediaPath();
        $mediaUrl = $mediaPath === '' ? '/media' : '/media/' . $mediaPath;
        $mediaDirectory = APP_DIR . '/media' . ($mediaPath === '' ? '' : '/' . $mediaPath);
        foreach ([APP_DIR . '/media', $mediaDirectory, APP_DIR . '/images'] as $directory) {
            if (is_link($directory)) { throw new \Nette\Application\ForbiddenRequestException('Unsafe file-manager root.'); }
        }

        // Outside the document root and, unlike the system temp, normally on the
        // same filesystem as the roots: extract into a new folder renames across it.
        // If it can't be created, elFinder falls back to the system temp.
        $tempDirectory = dirname(APP_DIR) . '/temp/elfinder';
        if (!is_dir($tempDirectory)) {
            @mkdir($tempDirectory, 0775, true);
        }
        $roots = [realpath($mediaDirectory), realpath(APP_DIR . '/images')];

        // Prepared-ZIP names this session made, so the Chrome-on-iOS fetch-by-name
        // can't be driven from another session (see beforeCommand, zipdl).
        $zipSession = $presenter->getSession('Caloris.Elfinder.Zipdl');

        // A '//absolute/path' forged hash and the out-of-root extraction temp path
        // both reach attr() as a '//...'-prefixed relpath, so no attributes regexp
        // can tell them apart. This callback canonicalises the real path: anything
        // inside a root reached that way - the root itself included - is hidden,
        // locked and denied read/write, while the extraction temp path (outside
        // both roots) is left to acceptedName and the defaults.
        $accessControl = static function ($attr, $path, $data, $volume, $isDir, $relpath) use ($roots) {
            return self::accessDecision((string) $attr, (string) $path, is_string($relpath) ? $relpath : '', $roots);
        };

        // The folders that already exist where an extract lands, taken just before it
        // runs: the after-handler changes the mode only of folders the extract made.
        $extractExisting = [];

        $opts = [
            'debug' => false,
            // In a non-UTF-8 locale PHP 7.4's basename() drops leading non-ASCII
            // characters: "Čáp.png" was listed as "p.png", and a second upload
            // replaced the file instead of getting a -1 suffix. elFinder's default,
            // en_US.UTF-8, isn't installed everywhere.
            'locale' => setlocale(LC_CTYPE, 'C.UTF-8', 'C.utf8', 'en_US.UTF-8', 'en_US.utf8', 'cs_CZ.UTF-8', 'cs_CZ.utf8') ?: '',
            'commonTempPath' => $tempDirectory,
            // Through '*': a named key such as 'upload.pre' only runs when $_POST names
            // the command, but the connector also takes it from a POST's query string.
            'bind' => [
                '*.pre' => [static function ($cmd, &$args, elFinder $finder) use ($roots, $zipSession, &$extractExisting): ?array {
                    if ($cmd === 'extract') {
                        $extractExisting = self::existingFolders($finder, $args['target'] ?? null);
                    }
                    return self::beforeCommand($cmd, $args, $finder, $roots, $zipSession);
                }],
                // Record each prepared ZIP's name so a later fetch can be bound to
                // the session that prepared it (only the CriOS name is session-free).
                'zipdl' => [static function ($cmd, &$result) use ($zipSession): void {
                    self::rememberPreparedZip($result, $zipSession);
                }],
                // After every command, registered for the same reason as '*.pre'.
                '*' => [static function ($cmd, &$result, $args, elFinder $finder) use ($roots, &$extractExisting): void {
                    self::afterCommand((string) $cmd, $result, $finder, $roots, $extractExisting);
                }],
            ],
            // Keep the Nette session open: elFinder's early close/reopen replaces
            // its cookie settings and can lose the login on local HTTP requests.
            'sessionCloseEarlier' => false,
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path' => $mediaDirectory,                 // path to files (REQUIRED)
                    'URL' => $mediaUrl, // URL to files (REQUIRED)
                    // Page-owned files must be changed through MediaStorage.
                    'defaults' => ['read' => true, 'write' => false],
                    'uploadDeny' => ['all'],                // All Mimetypes not allowed to upload
                    'uploadAllow' => ['image', 'text/plain'],// Mimetype `image` and `text/plain` allowed to upload
                    'uploadOrder' => ['deny', 'allow'],      // allowed Mimetype `image` and `text/plain` only
                    'fileMode' => 0644,
                    'uploadOverwrite' => false,
                    'copyOverwrite' => false,
                    // A crafted cmd=url&options[onetime]=1 wrote a pointer in the
                    // temp directory that cmd=file&onetime=1 then served; images
                    // are inserted by their direct URL, so one-time links are unused.
                    'onetimeUrl' => false,
                    'dispInlineRegex' => self::DISP_INLINE,
                    'acceptedName' => self::ACCEPTED_NAME,
                    'accessControl' => $accessControl,
                    'attributes' => $this->getHiddenDirectories()
                ],
                [
                    'driver' => 'LocalFileSystem',
                    'path' => APP_DIR . '/images',
                    'URL' => '/images',
                    'uploadDeny' => ['all'],
                    'uploadAllow' => ['image', 'text/plain'],
                    'uploadOrder' => ['deny', 'allow'],
                    'fileMode' => 0644,
                    'uploadOverwrite' => false,
                    'copyOverwrite' => false,
                    'onetimeUrl' => false,
                    'dispInlineRegex' => self::DISP_INLINE,
                    'acceptedName' => self::ACCEPTED_NAME,
                    'accessControl' => $accessControl,
                    'attributes' => $this->getHiddenDirectories()
                ]
            ]
        ];

        // Each root has its own permission; access to one must not expose the other.
        foreach ($permissions as $index => $permission) {
            if (!AdminPermissions::allows($role, $permission)) {
                unset($opts['roots'][$index]);
            }
        }
        $opts['roots'] = array_values($opts['roots']);

        // Run elFinder
        // Serialize independent connector mutations with standalone asset writes.
        $assetLock = \App\Model\AssetStorage::lock(APP_DIR);
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }

    /** @param \Nette\Http\SessionSection|object $zipSession holds the prepared-ZIP names for this session */
    private static function beforeCommand(string $cmd, array &$args, elFinder $finder, array $roots, $zipSession): ?array
    {
        if ($cmd === 'upload') {
            // The client may send overwrite=1, which elFinder applies over
            // uploadOverwrite; a duplicate name is always renamed instead.
            $args['overwrite'] = '0';
            // The client sends upload[] as a flat list. A nested field
            // (upload[a][b]) puts arrays where names belong: a TypeError on PHP 8.
            $names = $_FILES['upload']['name'] ?? [];
            $names = is_array($names) ? $names : [$names];
            foreach ($names as $name) {
                if (!is_string($name)) {
                    return self::refuse(elFinder::ERROR_UPLOAD, 'Neplatný upload.');
                }
            }
            // A chunk names a file in elFinder's temp directory. A part upload
            // carries a file and is "<name>.<n>_<total>.part"; the merge commit
            // carries no file and may read back only elFinder's own ELF... name.
            // Any other name would pull an arbitrary temp file into the volume.
            // The client's cleanup after a failed chunk carries no file and names
            // the part; elFinder then deletes only that upload's own parts.
            $chunk = (string) ($args['chunk'] ?? '');
            if ($chunk !== '') {
                $hasFile = array_filter($names, static function (string $name): bool {
                    return $name !== '';
                }) !== [];
                $isPart = (bool) preg_match('/^.+\.\d+_\d+\.part$/s', $chunk);
                $isCleanup = ($args['upload'] ?? null) === ['chunkfail'] && ($args['mimes'] ?? null) === 'chunkfail';
                $accepted = $hasFile || $isCleanup
                    ? $isPart
                    : (bool) preg_match('/^ELF[0-9A-Za-z]+(?:\.part)?$/D', $chunk);
                if (!$accepted) {
                    return self::refuse(elFinder::ERROR_UPLOAD, 'Neplatný upload.');
                }
            }
        } elseif ($cmd === 'file') {
            // One-time links are off (onetimeUrl), but elFinder still serves
            // cmd=file&onetime=1 from a pointer file in its temp directory and
            // deletes the file it names; a pointer planted by a partial chunk
            // upload made that reach another temp file. Nothing uses them.
            if (!empty($args['onetime'])) {
                return self::notFound();
            }
        } elseif ($cmd === 'duplicate') {
            // The suffix becomes part of the new name but skips acceptedName.
            if (isset($args['suffix']) && preg_match('/\p{Cc}/u', (string) $args['suffix'])) {
                return self::refuse(elFinder::ERROR_COPY, 'Neplatný název.');
            }
            if (self::duplicateRefused($finder, $args['targets'] ?? [], $roots)) {
                return self::refuse(elFinder::ERROR_COPY, elFinder::ERROR_PERM_DENIED);
            }
        } elseif ($cmd === 'extract') {
            // An archive sitting directly in a root extracts in place and can merge
            // into a managed folder (e.g. carousel), past its locked attribute.
            // Force a new, uniquely named folder there instead.
            $path = $finder->realpath((string) ($args['target'] ?? ''));
            if (is_string($path) && in_array(dirname($path), $roots, true)) {
                $args['makedir'] = '1';
            }
        } elseif ($cmd === 'archive') {
            $targets = (array) ($args['targets'] ?? []);
            // All targets must share one parent: archive() otherwise takes the
            // folder from the first and only the basename of the rest, zipping a
            // same-named sibling (which may hold a dot file) in place of a target.
            $parents = [];
            foreach ($targets as $hash) {
                $path = $finder->realpath($hash);
                if (is_string($path)) {
                    $parents[dirname($path)] = true;
                }
            }
            if (count($parents) > 1) {
                return self::refuse(elFinder::ERROR_ARCHIVE, 'Vybrané položky musí být ve stejné složce.');
            }
            // elFinder zips a tree natively, past the attributes and following
            // symlinks, and archive saves the ZIP publicly: refuse a whole root, a
            // dot file anywhere inside, a symlink, or an unreadable subfolder.
            if (self::archiveUnsafe($finder, $targets, $roots)) {
                return self::refuse(elFinder::ERROR_ARCHIVE,
                    'Složku nelze zabalit: obsahuje skryté soubory (např. .htaccess), odkaz (symlink) nebo nečitelnou podsložku.');
            }
            // Without a given name the ZIP is named after the folder; a control
            // character in that name would enter a new file name.
            if ((string) ($args['name'] ?? '') === '') {
                foreach ($targets as $hash) {
                    $path = $finder->realpath($hash);
                    if (is_string($path) && preg_match('/\p{Cc}/u', basename($path))) {
                        return self::refuse(elFinder::ERROR_ARCHIVE, 'Neplatný název.');
                    }
                }
            }
        } elseif ($cmd === 'zipdl') {
            if (empty($args['download'])) {
                // Preparing a ZIP. getItemsInHand checks read on every item, so a
                // dot file, a managed folder and an unreadable item are skipped and
                // don't leak; but zip and tar follow a symlink out of the root.
                if (self::treeHasSymlink($finder, (array) ($args['targets'] ?? []))) {
                    return self::refuse(elFinder::ERROR_ARCHIVE,
                        'Složku nelze stáhnout: obsahuje odkaz (symlink).');
                }
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'CriOS') !== false) {
                // Chrome on iOS fetches a ZIP twice, so for it elFinder takes the
                // file from the request instead of the session
                // (elFinder.class.php:1951). Accept only a prepared-ZIP name that
                // this session prepared, so another session can't fetch or delete it.
                $file = ((array) ($args['targets'] ?? []))[1] ?? null;
                $prepared = $zipSession->prepared ?? [];
                if (!is_string($file) || !preg_match(self::PREPARED_ZIP, $file)
                    || !in_array($file, is_array($prepared) ? $prepared : [], true)) {
                    return self::notFound();
                }
            }
        }
        return null;
    }

    private static function refuse(string $error, string $message): array
    {
        return ['preventexec' => true, 'results' => ['error' => [$error, $message]]];
    }

    /** The raw 404 elFinder itself answers for a file it can't serve. */
    private static function notFound(): array
    {
        return ['preventexec' => true, 'results' => ['error' => 'File not found',
            'header' => 'HTTP/1.x 404 Not Found', 'raw' => true]];
    }

    /**
     * duplicate copies each target next to itself after checking only that it exists
     * and that the new name may be created. A dot file named with letters and digits
     * only (.env, .htaccess) lost its dot in the new name (" copy 1.env"), which was
     * then listed, readable and served at its direct URL, and the read-only media root
     * got new files. As the client requires before it offers Duplikovat: every target
     * is readable, visible and not a root, and the folder it is copied into is
     * writable. Otherwise nothing is duplicated.
     *
     * @param mixed $targets
     */
    private static function duplicateRefused(elFinder $finder, $targets, array $roots): bool
    {
        if (!is_array($targets)) {
            return false; // elFinder duplicates nothing unless targets is a list.
        }
        foreach ($targets as $hash) {
            $volume = is_string($hash) ? $finder->getVolume($hash) : false;
            $stat = $volume ? $volume->file($hash) : false;
            if (!is_array($stat) || empty($stat['read']) || !empty($stat['hidden']) || empty($stat['phash'])) {
                return true;
            }
            $real = realpath((string) $finder->realpath($hash));
            if ($real !== false && in_array($real, $roots, true)) {
                return true;
            }
            $parent = $volume->file($stat['phash']);
            if (!is_array($parent) || empty($parent['write']) || empty($parent['read']) || !empty($parent['hidden'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * The real paths of the folders already in the folder that an extract of $target
     * lands in. In-place extract lists a folder it merged into as added, too.
     *
     * @param mixed $target
     * @return array<string, true>
     */
    private static function existingFolders(elFinder $finder, $target): array
    {
        $path = is_string($target) ? $finder->realpath($target) : false;
        $dir = is_string($path) ? dirname($path) : '';
        $entries = $dir !== '' && is_dir($dir) ? @scandir($dir) : false;
        $existing = [];
        foreach (is_array($entries) ? $entries : [] as $entry) {
            $child = $dir . DIRECTORY_SEPARATOR . $entry;
            if ($entry !== '.' && $entry !== '..' && is_dir($child) && ($real = realpath($child)) !== false) {
                $existing[$real] = true;
            }
        }
        return $existing;
    }

    /**
     * Extract into a new folder renames elFinder's quarantine folder into place, and
     * that folder is 0777. A folder the extract made gets the mode of one made in the
     * File Manager instead; a folder that existed before (merged into in place) keeps
     * its mode.
     *
     * @param array<string, true> $existing  from existingFolders(), just before the extract
     */
    private static function afterCommand(string $cmd, $result, elFinder $finder, array $roots, array $existing): void
    {
        if ($cmd !== 'extract' || !is_array($result) || empty($result['added']) || !is_array($result['added'])) {
            return;
        }
        foreach ($result['added'] as $stat) {
            if (!is_array($stat) || ($stat['mime'] ?? '') !== 'directory' || !is_string($stat['hash'] ?? null)) {
                continue;
            }
            $path = $finder->realpath($stat['hash']);
            if (!is_string($path) || is_link($path) || !is_dir($path) || ($real = realpath($path)) === false
                || isset($existing[$real])) {
                continue;
            }
            foreach ($roots as $root) {
                if (is_string($root) && strpos($real, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) === 0) {
                    if ((fileperms($real) & 0777) !== 0755) {
                        @chmod($real, 0755);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Access decision for a path that reaches attr() unstripped. elFinder prepends '/'
     * to the root-relative path, so every canonical in-root path, the root included,
     * arrives as '/...' and is left to the attributes rules. A path arrives as '//...'
     * only when it doesn't start with the volume root: the extraction temp directory,
     * or a forged '//absolute/path' hash. elFinder locks only the canonical root path,
     * so a forged spelling of the root could be removed or renamed, and a forged
     * extract target skipped the forced new folder. Such a path is never legitimate
     * inside a root: canonicalised, anything that lands in a root - the root itself,
     * a dot file, a managed folder or an ordinary file - is hidden, locked and denied
     * read/write. A path outside both roots (the temp directory, also with a dot in
     * its path) is left to acceptedName and the defaults, so extract still works.
     *
     * @return bool|null  the attribute value, or null to defer
     */
    private static function accessDecision(string $attr, string $path, string $relpath, array $roots): ?bool
    {
        static $deny = ['read' => false, 'write' => false, 'hidden' => true, 'locked' => true];
        if (!isset($deny[$attr]) || strpos($relpath, '//') !== 0) {
            return null;
        }
        // The path as spelled, and as resolved through its nearest existing ancestor
        // (symlinks such as /proc/self/cwd, and a new name in a folder not yet made).
        $candidates = [preg_replace('~/+~', '/', '/' . ltrim($path, '/'))];
        $probe = $path;
        $missing = '';
        while (($real = @realpath($probe)) === false) {
            $parent = dirname($probe);
            if ($parent === $probe) {
                break;
            }
            $missing = DIRECTORY_SEPARATOR . basename($probe) . $missing;
            $probe = $parent;
        }
        if ($real !== false) {
            $candidates[] = rtrim($real, DIRECTORY_SEPARATOR) . $missing;
        }
        foreach ($candidates as $candidate) {
            foreach ($roots as $root) {
                if (!is_string($root)) {
                    continue;
                }
                $root = rtrim($root, DIRECTORY_SEPARATOR);
                if ($candidate === $root || strpos($candidate, $root . DIRECTORY_SEPARATOR) === 0) {
                    return $deny[$attr];
                }
            }
        }
        return null;
    }

    /** A whole root, or a tree with a dot file, a symlink or an unreadable subfolder. */
    private static function archiveUnsafe(elFinder $finder, array $targets, array $roots): bool
    {
        foreach ($targets as $hash) {
            $path = $finder->realpath($hash);
            if (!is_string($path)) {
                continue; // elFinder itself answers an unknown or unreadable target.
            }
            if (in_array($path, $roots, true) || self::treeUnsafe($path)) {
                return true;
            }
        }
        return false;
    }

    /** The target itself, or anything inside it, is a dot entry, a symlink or unreadable. */
    private static function treeUnsafe(string $path): bool
    {
        if (is_link($path)) {
            return true;
        }
        $name = basename($path);
        if ($name !== '' && $name[0] === '.') {
            return true;
        }
        if (is_dir($path)) {
            $handle = @opendir($path);
            if ($handle === false) {
                return true; // unreadable subfolder: refuse rather than throw a 500 (D7).
            }
            try {
                while (($entry = readdir($handle)) !== false) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    if ($entry[0] === '.') {
                        return true;
                    }
                    $child = $path . DIRECTORY_SEPARATOR . $entry;
                    if (is_link($child) || (is_dir($child) && self::treeUnsafe($child))) {
                        return true;
                    }
                }
            } finally {
                closedir($handle);
            }
        }
        return false;
    }

    /** A symlink at or anywhere inside any target (zip/tar would follow it out of the root). */
    private static function treeHasSymlink(elFinder $finder, array $targets): bool
    {
        foreach ($targets as $hash) {
            $path = $finder->realpath($hash);
            if (is_string($path) && self::pathHasSymlink($path)) {
                return true;
            }
        }
        return false;
    }

    private static function pathHasSymlink(string $path): bool
    {
        if (is_link($path)) {
            return true;
        }
        if (is_dir($path)) {
            $handle = @opendir($path);
            if ($handle === false) {
                return false; // unreadable: getItemsInHand skips it, so it can't leak.
            }
            try {
                while (($entry = readdir($handle)) !== false) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    $child = $path . DIRECTORY_SEPARATOR . $entry;
                    if (is_link($child) || (is_dir($child) && self::pathHasSymlink($child))) {
                        return true;
                    }
                }
            } finally {
                closedir($handle);
            }
        }
        return false;
    }

    /** Remember a prepared ZIP's name for this session (only the CriOS name is session-free). */
    /** @param \Nette\Http\SessionSection|object $zipSession holds the prepared-ZIP names for this session */
    private static function rememberPreparedZip(&$result, $zipSession): void
    {
        if (empty($result['zipdl']['file']) || !is_string($result['zipdl']['file'])
            || !preg_match(self::PREPARED_ZIP, $result['zipdl']['file'])) {
            return;
        }
        $prepared = $zipSession->prepared ?? [];
        if (!is_array($prepared)) {
            $prepared = [];
        }
        $prepared[] = $result['zipdl']['file'];
        $zipSession->prepared = array_slice(array_values(array_unique($prepared)), -20);
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
            ['pattern' => '!^/(?:tn|\.tmb|\.quarantine|admin|menu|paths|carousel|profiles)(?:/|$)!',
                'hidden' => true, 'read' => false, 'write' => false, 'locked' => true],
            // Any name starting with a dot, at any depth: www/images/.htaccess
            // is what blocks scripts and SVG under /images. elFinder still
            // writes its own .tmb thumbnails directly. A path outside the root
            // reaches this as '//absolute/path' and must not match: extraction
            // checks files in elFinder's temp directory, which may have a dot in
            // its path (dot entries are refused there by acceptedName). The s
            // modifier: a folder already named with a newline must not hide one.
            ['pattern' => '~^/(?!/)(?:.*/)?\.~s', 'hidden' => true, 'read' => false, 'write' => false, 'locked' => true],
        ];

    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->render();
    }

}
