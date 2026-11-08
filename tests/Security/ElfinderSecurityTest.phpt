<?php
declare(strict_types=1);

use Tester\Assert;
require __DIR__ . '/../bootstrap.php';

// No DNS/socket access is permitted in either branch of this regression.
class RemoteUploadProbe extends elFinder
{
    public function __construct() {}
    protected function validate_address($url) { throw new LogicException('Unexpected DNS validation'); }
    protected function fsock_get_contents(&$url, $timeout, $redirect_max, $ua, $outfp, $info) { throw new LogicException('Unsafe socket fallback'); }
    public function fetchWithoutCurl(): bool {
        $url = 'http://example.invalid/fixture.txt';
        return $this->get_remote_contents($url);
    }
}
if (($argv[1] ?? '') === '--no-curl') {
    Assert::false(function_exists('curl_exec'));
    Assert::false((new RemoteUploadProbe)->fetchWithoutCurl());
    exit;
}
$output = [];
exec(escapeshellarg(PHP_BINARY) . ' -d disable_functions=curl_init,curl_exec ' . escapeshellarg(__FILE__) . ' --no-curl 2>&1', $output, $status);
Assert::same(0, $status, implode("\n", $output));

$connector = (new ReflectionClass(elFinderConnector::class))->newInstanceWithoutConstructor();
$protected = new ReflectionMethod($connector, 'csrfProtectedCommand');
$protected->setAccessible(true);
Assert::true($protected->invoke($connector, 'netmount'));

$root = sys_get_temp_dir() . '/caloris-elfinder-' . bin2hex(random_bytes(8));
mkdir($root);
try {
    $finder = new elFinder(['sessionCloseEarlier' => false, 'roots' => [[
        'driver' => 'LocalFileSystem', 'path' => $root, 'uploadDeny' => ['all'],
        'uploadAllow' => ['image', 'text/plain'], 'uploadOrder' => ['deny', 'allow'],
        'tmbPath' => '', 'quarantine' => '',
    ]]]);
    $volumesProperty = new ReflectionProperty($finder, 'volumes');
    $volumesProperty->setAccessible(true);
    $volumes = $volumesProperty->getValue($finder);
    Assert::count(1, $volumes);
    $volume = reset($volumes);
    foreach (['phtml', 'phar', 'php5', 'php3', 'txt'] as $ext) {
        file_put_contents($root . '/fixture.' . $ext, 'Inert test data');
    }
    $check = new ReflectionMethod($volume, 'checkExtractItems');
    $check->setAccessible(true);
    $result = $check->invoke($volume, $root, ['mime']);
    Assert::count(4, $result['mimes']);
    foreach (['phtml', 'phar', 'php5', 'php3'] as $ext) { Assert::false(file_exists($root . '/fixture.' . $ext)); }
    Assert::same('Inert test data', file_get_contents($root . '/fixture.txt'));
} finally {
    Nette\Utils\FileSystem::delete($root);
}

// The dot-file rule covers paths inside a root only. Extraction checks files in
// elFinder's temp directory, which falls under vendor/.../.tmp when that is
// writable; matching it made extract silently produce nothing. Dot entries are
// still dropped, by acceptedName.
$rules = (new Caloriscz\Utilities\ElfinderControl)->getHiddenDirectories();
$dotRule = $rules[1]['pattern'];
foreach (['/.htaccess', '/a/.x', '/a/b/.c/d', '/.tmb', '/a/.b/c.txt', "/a\nb/.x", "/\n/.x", "/a\nb/c\nd/.e/f"] as $path) {
    Assert::same(1, preg_match($dotRule, $path), $path);
}
foreach (['/', '/a', '/a/b.c', '/a.b/c', '/a/b..c', '//var/www/vendor/php/.tmp/x', '//home/.sites/x/y.txt',
        "/a\nb", "/a\nb/c.txt", "/a\n.b", "//home/a\nb/.tmp/x"] as $path) {
    Assert::same(0, preg_match($dotRule, $path), $path);
}
// Negative control: without the s modifier a dot file in a folder with a newline
// in its name doesn't match, so the newline cases above do exercise it.
Assert::same(0, preg_match(rtrim($dotRule, 's'), "/a\nb/.x"));
// The connector's accessControl callback, built as handleOptions() builds it.
$accessDecision = new ReflectionMethod(Caloriscz\Utilities\ElfinderControl::class, 'accessDecision');
$accessDecision->setAccessible(true);
$callbackFor = static function (array $roots) use ($accessDecision): Closure {
    return static function ($attr, $path, $data, $volume, $isDir, $relpath) use ($accessDecision, $roots) {
        return $accessDecision->invoke(null, (string) $attr, (string) $path, is_string($relpath) ? $relpath : '', $roots);
    };
};
$base = sys_get_temp_dir() . '/caloris-elfinder-' . bin2hex(random_bytes(8));
$root = $base . '/root';
$extracted = $base . '/.tmp/extracted';
Nette\Utils\FileSystem::createDir($root);
Nette\Utils\FileSystem::createDir($extracted . '/sub');
try {
    foreach (['a.txt', '.x', 'sub/b.txt', 'sub/.x', "n\nl.txt"] as $name) {
        file_put_contents($extracted . '/' . $name, 'Inert test data');
    }
    $finder = new elFinder(['sessionCloseEarlier' => false, 'commonTempPath' => $base . '/.tmp', 'roots' => [[
        'driver' => 'LocalFileSystem', 'path' => $root, 'uploadDeny' => ['all'],
        'uploadAllow' => ['image', 'text/plain'], 'uploadOrder' => ['deny', 'allow'],
        'tmbPath' => '', 'attributes' => $rules, 'acceptedName' => Caloriscz\Utilities\ElfinderControl::ACCEPTED_NAME,
        // With the callback: the temp path (a dot in it, outside the root) must
        // stay deferred, or every extracted file would be dropped as unwritable.
        'accessControl' => $callbackFor([realpath($root)]),
    ]]]);
    $volumes = $volumesProperty->getValue($finder);
    $volume = reset($volumes);
    $check->invoke($volume, $extracted);
    Assert::same('Inert test data', file_get_contents($extracted . '/a.txt'));
    Assert::same('Inert test data', file_get_contents($extracted . '/sub/b.txt'));
    Assert::false(file_exists($extracted . '/.x'));
    Assert::false(file_exists($extracted . '/sub/.x'));
    Assert::false(file_exists($extracted . "/n\nl.txt"));

    // New names: control characters and a leading dot are refused, for files and
    // folders; ordinary names, also non-ASCII ones, are not.
    $nameAccepted = new ReflectionMethod($volume, 'nameAccepted');
    $nameAccepted->setAccessible(true);
    foreach ([false, true] as $isDir) {
        foreach (['a.txt', 'Čáp řeka.png', 'a b.c', 'a..b', "it's (1).png", '😀.png'] as $name) {
            Assert::truthy($nameAccepted->invoke($volume, $name, $isDir), $name);
        }
        foreach (['.x', '.htaccess', "a\nb", "\nx", "x\n", "a\rb", "a\tb", "a\x7Fb", "a\u{85}b", "a\0b", 'a/b', ''] as $name) {
            Assert::falsey($nameAccepted->invoke($volume, $name, $isDir), json_encode($name));
        }
    }
} finally {
    Nette\Utils\FileSystem::delete($base);
}
$source = file_get_contents(__DIR__ . '/../../app/components/Utilities/ElfinderControl.php');
Assert::same(2, substr_count($source, "'acceptedName' => self::ACCEPTED_NAME"));

// A User-Agent with CriOS makes elFinder take the file from targets[1] and serve
// and delete it from its temp directory: only a prepared ZIP's name, and only one
// this very session prepared, gets through (owner decision 4).
$beforeCommand = new ReflectionMethod(Caloriscz\Utilities\ElfinderControl::class, 'beforeCommand');
$beforeCommand->setAccessible(true);
$zipSession = (object) ['prepared' => ['elfzdl-aB3xYz']]; // what this session prepared
$zipdl = static function (array $args) use ($beforeCommand, $finder, $zipSession): ?array {
    return $beforeCommand->invokeArgs(null, ['zipdl', &$args, $finder, [], $zipSession]);
};
$fetch = static function ($file): array {
    return ['download' => '1', 'targets' => ['l1_x', $file, 'x.zip', 'application/zip']];
};
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) CriOS/120.0 Mobile/15E148 Safari/604.1';
// Misshapen names, and a well-formed name this session did NOT prepare, are all 404.
foreach (['.', '..', '../x', '../elfzdl-aB3', 'ELFabc', 'elfzdl-', 'elfzdl-a/../b', "elfzdl-a\n", 'other-file',
        ['elfzdl-aB3'], 'elfzdl-NOTMINE'] as $file) {
    $refusal = $zipdl($fetch($file));
    Assert::true($refusal['preventexec'], json_encode($file));
    Assert::contains('404', $refusal['results']['header']);
}
Assert::null($zipdl($fetch('elfzdl-aB3xYz'))); // the one this session prepared
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0 Safari/537.36';
Assert::null($zipdl($fetch('65f1c2a7b3d4e'))); // Elsewhere targets[1] is only a session key.
unset($_SERVER['HTTP_USER_AGENT']);

// rememberPreparedZip records only elFinder's own CriOS basename (elfzdl-...), not
// the non-CriOS uniqid, and keeps at most the last 20.
$remember = new ReflectionMethod(Caloriscz\Utilities\ElfinderControl::class, 'rememberPreparedZip');
$remember->setAccessible(true);
$store = (object) [];
foreach (['elfzdl-aaa111', '65f1c2a7b3d4e', 'elfzdl-bbb222'] as $name) {
    $result = ['zipdl' => ['file' => $name, 'name' => 'x.zip', 'mime' => 'application/zip']];
    $remember->invokeArgs(null, [&$result, $store]);
}
Assert::same(['elfzdl-aaa111', 'elfzdl-bbb222'], $store->prepared);

// A forged '//absolute/path' hash: elFinder locks only the canonical root path, and
// the attributes rules can't match such a path (it arrives as a '///...' relpath,
// which (?!/) refuses). accessDecision() canonicalises it: anything that lands in a
// root - the root itself, a dot file, a managed folder or an ordinary file - is
// hidden, locked and denied read/write. A path outside the roots, such as the
// extraction temp directory (also with a dot in its path), is deferred.
$acBase = sys_get_temp_dir() . '/caloris-ac-' . bin2hex(random_bytes(8));
$acRoot = $acBase . '/images';
$acTmp = $acBase . '/.tmp/elfinder';
Nette\Utils\FileSystem::createDir($acRoot . '/carousel');
Nette\Utils\FileSystem::createDir($acRoot . '/sub');
Nette\Utils\FileSystem::createDir($acTmp);
try {
    file_put_contents($acRoot . '/.htaccess', "Require all denied\n");
    file_put_contents($acRoot . '/visible.txt', 'ok');
    file_put_contents($acRoot . '/removable.txt', 'ok');
    file_put_contents($acTmp . '/.x', 'extracted dot entry');
    file_put_contents($acTmp . '/plain.txt', 'extracted');
    $realRoot = realpath($acRoot);
    $roots = [$realRoot];
    $decide = static function (string $attr, string $abs) use ($accessDecision, $roots) {
        // elFinder prepends '/' to a relpath it could not strip to the root.
        return $accessDecision->invoke(null, $attr, $abs, '/' . $abs, $roots);
    };
    $denied = static function (string $abs) use ($decide): void {
        Assert::false($decide('read', $abs), $abs);
        Assert::false($decide('write', $abs), $abs);
        Assert::true($decide('hidden', $abs), $abs);
        Assert::true($decide('locked', $abs), $abs);
    };
    // The root itself (N1), a dot file, a managed folder and a new file in it, an
    // ordinary file and folder, and new names one and three levels deep.
    foreach (['', '/', '/.htaccess', '/carousel', '/carousel/evil.txt', '/visible.txt', '/sub',
            '/new.txt', '/no/such/dir/x.txt'] as $rel) {
        $denied('/' . $acRoot . $rel);   // the two leading slashes a forged hash keeps
        $denied('//' . $acRoot . $rel);  // three, as a unit case only (elFinder collapses them)
    }
    // Through a symlinked alias of the root's parent (Linux only).
    if (DIRECTORY_SEPARATOR === '/') {
        symlink($acBase, $acBase . '-alias');
        try {
            foreach (['/images', '/images/visible.txt', '/images/.htaccess', '/images/no/such/x'] as $rel) {
                $denied('/' . $acBase . '-alias' . $rel);
            }
        } finally {
            unlink($acBase . '-alias');
        }
    }
    // Outside the roots: deferred for every attribute, also with a dot in the path.
    foreach (['/' . $acTmp . '/.x', '/' . $acTmp . '/plain.txt', '/' . $acTmp . '/new/deep.txt',
            '/' . $acBase, '//etc/hostname'] as $abs) {
        foreach (['read', 'write', 'hidden', 'locked'] as $attr) {
            Assert::null($decide($attr, $abs), $attr . ' ' . $abs);
        }
        // ... and the attributes rules don't match it either.
        foreach ($rules as $rule) {
            Assert::same(0, preg_match($rule['pattern'], '/' . $abs), $abs);
        }
    }
    // Canonical in-root paths arrive as '/...' relpaths: deferred to the rules.
    Assert::null($accessDecision->invoke(null, 'read', $acRoot . '/visible.txt', '/visible.txt', $roots));
    Assert::null($accessDecision->invoke(null, 'locked', $realRoot, '/', $roots));
    // Only the four permission attributes are decided here.
    Assert::null($decide('mime', '/' . $acRoot));

    // The same through a real elFinder volume with the callback, on a disposable root.
    $acFinder = new elFinder(['sessionCloseEarlier' => false, 'commonTempPath' => $acBase . '/.tmp', 'roots' => [[
        'driver' => 'LocalFileSystem', 'path' => $acRoot, 'tmbPath' => '', 'attributes' => $rules,
        'acceptedName' => Caloriscz\Utilities\ElfinderControl::ACCEPTED_NAME, 'accessControl' => $callbackFor($roots),
    ]]]);
    $acVolumes = $volumesProperty->getValue($acFinder);
    $acVolume = reset($acVolumes);
    $encode = new ReflectionMethod($acVolume, 'encode');
    $encode->setAccessible(true);
    $forge = static function (string $abs) use ($acVolume): string {
        return $acVolume->id() . rtrim(strtr(base64_encode($abs), '+/=', '-_.'), '.');
    };
    $canonicalRoot = $acVolume->file($acVolume->defaultPath());
    Assert::same(1, $canonicalRoot['locked']);
    // Exactly two leading slashes: _normpath keeps them, so the path is not the root.
    $forgedRoot = $forge('/' . $realRoot);
    $stat = $acVolume->file($forgedRoot);
    Assert::same([0, 0, 1, 1], [$stat['read'], $stat['write'], $stat['locked'], $stat['hidden']]);
    Assert::false($acVolume->rm($forgedRoot));
    Assert::false($acVolume->rename($forgedRoot, 'moved'));
    Assert::false($acVolume->mkdir($forgedRoot, 'planted'));
    Assert::false($acVolume->rm($forge('/' . $realRoot . '/visible.txt')));
    Assert::false($acVolume->rm($forge('/' . $realRoot . '/.htaccess')));
    Assert::same("Require all denied\n", file_get_contents($acRoot . '/.htaccess'));
    Assert::same('ok', file_get_contents($acRoot . '/visible.txt'));
    Assert::false(file_exists($acRoot . '/planted') || file_exists($acBase . '/moved'));
    // The canonical spelling of an ordinary file is unaffected.
    $plain = $acVolume->file($encode->invoke($acVolume, $realRoot . '/removable.txt'));
    Assert::same([1, 1], [$plain['read'], $plain['write']]);
    Assert::truthy($acVolume->rm($encode->invoke($acVolume, $realRoot . '/removable.txt')));
    Assert::false(file_exists($acRoot . '/removable.txt'));

    // N6: an extracted new folder (elFinder's 0777 quarantine folder renamed into
    // place) gets 0755; other commands and folders outside the roots are untouched.
    if (DIRECTORY_SEPARATOR === '/') {
        $afterCommand = new ReflectionMethod(Caloriscz\Utilities\ElfinderControl::class, 'afterCommand');
        $afterCommand->setAccessible(true);
        Nette\Utils\FileSystem::createDir($acRoot . '/extracted');
        chmod($acRoot . '/extracted', 0777);
        $added = ['added' => [$acVolume->file($encode->invoke($acVolume, $realRoot . '/extracted'))]];
        $afterCommand->invoke(null, 'mkdir', $added, $acFinder, $roots, []);
        clearstatcache();
        Assert::same(0777, fileperms($acRoot . '/extracted') & 0777);
        $afterCommand->invoke(null, 'extract', $added, $acFinder, [], []);
        clearstatcache();
        Assert::same(0777, fileperms($acRoot . '/extracted') & 0777);
        $afterCommand->invoke(null, 'extract', $added, $acFinder, $roots, []);
        clearstatcache();
        Assert::same(0755, fileperms($acRoot . '/extracted') & 0777);
        $afterCommand->invoke(null, 'extract', ['added' => [['mime' => 'directory', 'hash' => $forgedRoot]]], $acFinder, $roots, []);
        $afterCommand->invoke(null, 'extract', ['error' => 'x'], $acFinder, $roots, []);
    }
} finally {
    Nette\Utils\FileSystem::delete($acBase);
}

// treeUnsafe / pathHasSymlink: archive and zipdl refuse a tree with a dot file, a
// symlink or an unreadable subfolder; a clean tree passes (Linux only - needs
// symlink and chmod).
if (DIRECTORY_SEPARATOR === '/') {
    $treeUnsafe = new ReflectionMethod(Caloriscz\Utilities\ElfinderControl::class, 'treeUnsafe');
    $treeUnsafe->setAccessible(true);
    $hasSymlink = new ReflectionMethod(Caloriscz\Utilities\ElfinderControl::class, 'pathHasSymlink');
    $hasSymlink->setAccessible(true);
    $tBase = sys_get_temp_dir() . '/caloris-tree-' . bin2hex(random_bytes(8));
    try {
        Nette\Utils\FileSystem::createDir($tBase . '/clean/sub');
        file_put_contents($tBase . '/clean/sub/a.txt', 'x');
        Assert::false($treeUnsafe->invoke(null, $tBase . '/clean'));
        Assert::false($hasSymlink->invoke(null, $tBase . '/clean'));
        Nette\Utils\FileSystem::createDir($tBase . '/dotty');
        file_put_contents($tBase . '/dotty/.secret', 'x');
        Assert::true($treeUnsafe->invoke(null, $tBase . '/dotty'));
        Nette\Utils\FileSystem::createDir($tBase . '/linky');
        symlink('/etc', $tBase . '/linky/etclink');
        Assert::true($treeUnsafe->invoke(null, $tBase . '/linky'));
        Assert::true($hasSymlink->invoke(null, $tBase . '/linky'));
        // root can open a 0000 dir, so this only holds for a non-root runtime (the
        // connector runs as www-data; elfinder-security-http.py covers it live).
        if (!function_exists('posix_getuid') || posix_getuid() !== 0) {
            Nette\Utils\FileSystem::createDir($tBase . '/noread/nope');
            chmod($tBase . '/noread/nope', 0000);
            Assert::true($treeUnsafe->invoke(null, $tBase . '/noread'));
            chmod($tBase . '/noread/nope', 0755);
        }
    } finally {
        @chmod($tBase . '/noread/nope', 0755);
        Nette\Utils\FileSystem::delete($tBase);
    }
}

// beforeCommand '*.pre' branches that need no volume: a chunk must be elFinder's own
// name (a '.part' with an upload, or an ELF... merge without one), and a duplicate's
// suffix must carry no control character.
$pre = static function (string $cmd, array $args) use ($beforeCommand, $finder, $zipSession): ?array {
    return $beforeCommand->invokeArgs(null, [$cmd, &$args, $finder, [], $zipSession]);
};
$_FILES = [];
Assert::type('array', $pre('upload', ['chunk' => 'planted-name']));            // arbitrary -> refused
Assert::null($pre('upload', ['chunk' => 'ELF' . str_repeat('a', 32)]));        // merge commit, no file
Assert::null($pre('upload', ['chunk' => '']));                                  // ordinary upload
$_FILES = ['upload' => ['name' => ['blob']]];
Assert::null($pre('upload', ['chunk' => 'photo.jpg.0_2.part']));                // a part, with a file
Assert::type('array', $pre('upload', ['chunk' => 'planted-name']));            // no .part, with a file
// The client's cleanup after a failed chunk (no file, upload[]=chunkfail,
// mimes=chunkfail, the part's name) is accepted (N4); a near miss is not.
$_FILES = [];
$cleanup = ['chunk' => 'photo.jpg.1_3.part', 'cid' => '7', 'upload' => ['chunkfail'], 'mimes' => 'chunkfail'];
Assert::null($pre('upload', $cleanup));
foreach ([['chunk' => 'planted-name'], ['chunk' => 'ELF' . str_repeat('a', 32)], ['upload' => ['chunkfail', 'x']],
        ['upload' => [1 => 'chunkfail']], ['upload' => 'chunkfail'], ['mimes' => ['chunkfail']], ['mimes' => '']] as $change) {
    Assert::type('array', $pre('upload', array_merge($cleanup, $change)), json_encode($change));
}
Assert::type('array', $pre('upload', ['chunk' => 'photo.jpg.1_3.part']));      // a part without a file
// A nested upload field is refused, with or without a chunk (N5: a TypeError on PHP 8).
$_FILES = ['upload' => ['name' => ['a' => ['b' => 'x.png']], 'tmp_name' => ['a' => ['b' => '/tmp/x']],
    'error' => ['a' => ['b' => 0]], 'size' => ['a' => ['b' => 1]], 'type' => ['a' => ['b' => 'image/png']]]];
Assert::type('array', $pre('upload', ['chunk' => 'photo.jpg.0_2.part']));
Assert::type('array', $pre('upload', ['chunk' => '']));
Assert::type('array', $pre('upload', $cleanup));
$_FILES = ['upload' => ['name' => 'x.png']];                                     // a single, unnested field
Assert::null($pre('upload', ['chunk' => '']));
$_FILES = [];
// cmd=file with a non-empty onetime answers 404, whatever the target (N3).
foreach (['1', 'yes', ['x']] as $onetime) {
    foreach (['x', 'l1_x', md5('1_p') . '.part'] as $target) {
        $refusal = $pre('file', ['target' => $target, 'onetime' => $onetime]);
        Assert::true($refusal['preventexec']);
        Assert::contains('404', $refusal['results']['header']);
    }
}
Assert::null($pre('file', ['target' => 'l1_x', 'onetime' => '']));
Assert::null($pre('file', ['target' => 'l1_x', 'onetime' => '0']));
Assert::null($pre('file', ['target' => 'l1_x']));
// A normal upload has its crafted overwrite=1 forced back to 0.
$upArgs = ['overwrite' => '1'];
$beforeCommand->invokeArgs(null, ['upload', &$upArgs, $finder, [], $zipSession]);
Assert::same('0', $upArgs['overwrite']);

// N8: duplicate copies a target next to itself after checking only that it exists
// and that the new name may be created. A dot file named with letters and digits
// only (.env, .htaccess) came out as a listed, readable " copy 1.env", and the
// read-only media root got new files. The '*.pre' handler refuses the whole request
// unless every target is readable, visible and not a root, in a writable folder -
// what the client checks before it offers Duplikovat. Run through elFinder's own
// exec() on two disposable roots set up like the connector's.
$dBase = sys_get_temp_dir() . '/caloris-dup-' . bin2hex(random_bytes(8));
$dMedia = $dBase . '/media';
$dImages = $dBase . '/images';
foreach (['/images/carousel', '/images/.tmb', '/images/plain/inner', '/images/plain/.hide', '/media/sub', '/.tmp'] as $dir) {
    Nette\Utils\FileSystem::createDir($dBase . $dir);
}
try {
    foreach (['/images/.env', '/images/.htaccess', '/images/visible.txt', '/images/carousel/c.txt',
            '/images/plain/a.txt', '/images/plain/.env', '/images/plain/.vrf-x', '/images/plain/inner/b.txt',
            '/images/plain/inner/.htaccess', '/images/plain/.hide/h.txt', '/media/a.txt', '/media/sub/s.txt'] as $file) {
        file_put_contents($dBase . $file, 'Inert test data');
    }
    $dRoots = [realpath($dMedia), realpath($dImages)];
    $dRoot = static function (string $path, array $extra) use ($rules, $callbackFor, $dRoots): array {
        return $extra + ['driver' => 'LocalFileSystem', 'path' => $path, 'tmbPath' => '', 'attributes' => $rules,
            'acceptedName' => Caloriscz\Utilities\ElfinderControl::ACCEPTED_NAME, 'accessControl' => $callbackFor($dRoots)];
    };
    $dFinder = new elFinder(['sessionCloseEarlier' => false, 'commonTempPath' => $dBase . '/.tmp', 'roots' => [
        $dRoot($dMedia, ['defaults' => ['read' => true, 'write' => false]]),   // as the media root
        $dRoot($dImages, []),
    ]]);
    $dFinder->bind('*.pre', static function ($cmd, &$args, elFinder $finder) use ($beforeCommand, $dRoots, $zipSession) {
        return $beforeCommand->invokeArgs(null, [$cmd, &$args, $finder, $dRoots, $zipSession]);
    });
    [$dMediaVolume, $dImagesVolume] = array_values($volumesProperty->getValue($dFinder));
    $dEncode = new ReflectionMethod(elFinderVolumeDriver::class, 'encode');
    $dEncode->setAccessible(true);
    $hashOf = static function (string $rel) use ($dEncode, $dMediaVolume, $dImagesVolume, $dRoots): string {
        [$top, $sub] = explode('/', $rel, 2) + [1 => ''];
        $real = $top === 'media' ? $dRoots[0] : $dRoots[1];
        return $dEncode->invoke($top === 'media' ? $dMediaVolume : $dImagesVolume, rtrim($real . '/' . $sub, '/'));
    };
    $forged = static function (string $abs) use ($dImagesVolume): string {   // a '//absolute/path' hash
        return $dImagesVolume->id() . rtrim(strtr(base64_encode('/' . $abs), '+/=', '-_.'), '.');
    };
    $tree = static function () use ($dBase): array {
        $all = [];
        $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dBase, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST);
        foreach ($items as $item) {
            $rel = substr((string) $item, strlen($dBase));
            if (strpos($rel, '/.tmp') !== 0) {
                $all[] = $rel;
            }
        }
        sort($all);
        return $all;
    };
    $duplicate = static function (array $targets) use ($dFinder): array {
        return $dFinder->exec('duplicate', ['targets' => $targets]);
    };
    $before = $tree();
    $refused = [
        'a letters-only dot file in a root' => ['images/.env'],
        '.htaccess in a root' => ['images/.htaccess'],
        'a letters-only dot file in a folder' => ['images/plain/.env'],
        '.htaccess two levels down' => ['images/plain/inner/.htaccess'],
        'a hyphenated dot file' => ['images/plain/.vrf-x'],
        'a dot folder' => ['images/plain/.hide'],
        'a file in a dot folder' => ['images/plain/.hide/h.txt'],
        'a managed folder' => ['images/carousel'],
        'a file in a managed folder' => ['images/carousel/c.txt'],
        '.tmb' => ['images/.tmb'],
        'the images root' => ['images'],
        'the media root' => ['media'],
        'a file in the read-only media root' => ['media/a.txt'],
        'a folder in the read-only media root' => ['media/sub'],
        'a file deeper in the media root' => ['media/sub/s.txt'],
        'an ordinary file listed with a dot file' => ['images/plain/a.txt', 'images/plain/.env'],
    ];
    foreach ($refused as $label => $rels) {
        $result = $duplicate(array_map($hashOf, $rels));
        Assert::same([elFinder::ERROR_COPY, elFinder::ERROR_PERM_DENIED], $result['error'] ?? null, $label);
        Assert::false(isset($result['added']), $label);
        Assert::same($before, $tree(), $label);
    }
    // Forged '//' spellings of an ordinary file, a dot file and the root.
    foreach (['/plain/a.txt', '/.env', ''] as $rel) {
        $result = $duplicate([$forged($dRoots[1] . $rel)]);
        Assert::same([elFinder::ERROR_COPY, elFinder::ERROR_PERM_DENIED], $result['error'] ?? null, $rel);
        Assert::same($before, $tree(), $rel);
    }
    // An unknown hash and a non-string target are refused too, before elFinder runs.
    $dPre = static function (array $args) use ($beforeCommand, $dFinder, $dRoots, $zipSession): ?array {
        return $beforeCommand->invokeArgs(null, ['duplicate', &$args, $dFinder, $dRoots, $zipSession]);
    };
    Assert::type('array', $dPre(['targets' => [$dImagesVolume->id() . 'bm9uZQ']]));
    Assert::type('array', $dPre(['targets' => [['x']]]));
    // The suffix becomes part of the new name but skips acceptedName.
    Assert::type('array', $dPre(['targets' => [$hashOf('images/plain/a.txt')], 'suffix' => "x\ny"]));
    Assert::null($dPre(['targets' => [$hashOf('images/plain/a.txt')], 'suffix' => 'copy']));

    // The control: an ordinary file and an ordinary folder in a writable folder are
    // duplicated, and the folder's copy holds no dot file at any depth.
    $result = $duplicate([$hashOf('images/plain/a.txt')]);
    Assert::false(isset($result['error']));
    Assert::same('Inert test data', file_get_contents($dImages . '/plain/a copy 1.txt'));
    $result = $duplicate([$hashOf('images/plain')]);
    Assert::false(isset($result['error']));
    Assert::same(['/images/plain copy 1', '/images/plain copy 1/a copy 1.txt', '/images/plain copy 1/a.txt',
        '/images/plain copy 1/inner', '/images/plain copy 1/inner/b.txt'],
        array_values(array_filter($tree(), static function (string $rel): bool {
            return strpos($rel, '/images/plain copy 1') === 0;
        })));

    // N11: an in-place extract lists a folder it merged into as added. Only a folder
    // the extract made gets 0755 (N6); one that existed before keeps its mode.
    if (DIRECTORY_SEPARATOR === '/') {
        $existingFolders = new ReflectionMethod(Caloriscz\Utilities\ElfinderControl::class, 'existingFolders');
        $existingFolders->setAccessible(true);
        $afterExtract = new ReflectionMethod(Caloriscz\Utilities\ElfinderControl::class, 'afterCommand');
        $afterExtract->setAccessible(true);
        Nette\Utils\FileSystem::createDir($dImages . '/x/exist');
        chmod($dImages . '/x/exist', 0700);
        file_put_contents($dImages . '/x/two.zip', 'not read here');
        $existing = $existingFolders->invoke(null, $dFinder, $hashOf('images/x/two.zip'));
        Assert::same([realpath($dImages . '/x/exist') => true], $existing);
        Assert::same([], $existingFolders->invoke(null, $dFinder, ['x']));
        Nette\Utils\FileSystem::createDir($dImages . '/x/fresh');   // what the extract made
        chmod($dImages . '/x/fresh', 0777);
        $added = ['added' => [$dImagesVolume->file($hashOf('images/x/exist')), $dImagesVolume->file($hashOf('images/x/fresh'))]];
        $afterExtract->invoke(null, 'extract', $added, $dFinder, $dRoots, $existing);
        clearstatcache();
        Assert::same(0700, fileperms($dImages . '/x/exist') & 0777);
        Assert::same(0755, fileperms($dImages . '/x/fresh') & 0777);
    }
} finally {
    Nette\Utils\FileSystem::delete($dBase);
}

// Both roots set onetimeUrl off and the forged-path access callback; the connector
// binds through '*.pre' (always registered) and records prepared ZIPs via 'zipdl'.
Assert::same(2, substr_count($source, "'onetimeUrl' => false"));
Assert::same(2, substr_count($source, "'accessControl' => \$accessControl"));
Assert::same(1, preg_match("~'bind' =>.*?'\\*\\.pre'.*?'zipdl' =>~s", $source));
// The after-handler (extract folder mode) is bound through '*' too: a named key
// registers only when $_POST names the command. It gets the folders that existed
// before the extract from the '*.pre' handler (N11).
Assert::same(1, preg_match("~'\\*' => \\[static function \\(\\\$cmd, &\\\$result, \\\$args, elFinder \\\$finder\\) use \\(\\\$roots, &\\\$extractExisting\\)~", $source));
Assert::same(1, preg_match("~'\\*\\.pre' => \\[static function .*? use \\(\\\$roots, \\\$zipSession, &\\\$extractExisting\\).*?\\\$extractExisting = self::existingFolders\\(~s", $source));

// The CMS token exemption decides from the PHP superglobals elFinder's connector
// executes from (REQUEST_METHOD, $_GET, $_POST, $_FILES), not Nette's parsed view,
// so a forged query key or X-HTTP-Method-Override can't desync them (D3).
$base = file_get_contents(__DIR__ . '/../../app/AdminModule/presenters/BasePresenter.php');
Assert::same(1, preg_match("~\\\$_SERVER\\['REQUEST_METHOD'\\]~", $base));
Assert::same(1, preg_match("~\\\$_GET\\['cmd'\\]~", $base));
Assert::same(1, preg_match('~\\$_POST === \\[\\] && \\$_FILES === \\[\\]~', $base));
Assert::same(1, preg_match("~\\(\\\$_GET\\['download'\\] \\?\\? null\\) === '1'~", $base));

// The connector reads its token only from a header. The client falls back to
// the header-less iframe upload unless dragUploadAllow selects XHR, so the
// admin dialog must keep XHR uploads and the CMS header.
$layout = file_get_contents(__DIR__ . '/../../app/AdminModule/templates/@layout.latte');
Assert::same(1, preg_match('~dialogelfinder\(\{.*?\}\)\.dialogelfinder~s', $layout, $dialog));
Assert::same(1, preg_match('~\bdragUploadAllow:\s*true\b~', $dialog[0]));
Assert::same(1, preg_match("~customHeaders:\s*\{\s*'X-CSRF-Token'~", $dialog[0]));
$getHeader = new ReflectionMethod($connector, 'getRequestHeader');
$getHeader->setAccessible(true);
$_POST['X-elFinder-CSRF'] = 'body-token';
Assert::same('', $getHeader->invoke($connector, 'X-elFinder-CSRF'));
