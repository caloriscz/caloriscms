<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || !is_file('/.dockerenv')) { throw new RuntimeException('Local Docker only.'); }
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/model/Media/MediaStorage.php';
$mode = $argv[1] ?? '';
$prefix = $argv[2] ?? '';
if (!preg_match('/^mediatest-[a-f0-9]{8}$/D', $prefix)) { throw new InvalidArgumentException('Invalid fixture prefix.'); }
$connection = new Nette\Database\Connection('mysql:host=db;dbname=caloriscms', 'caloris', 'caloris');
$db = new Nette\Database\Explorer($connection, new Nette\Database\Structure($connection, new Nette\Caching\Storages\MemoryStorage()));
$page = $db->table('pages')->where('slug', $prefix)->fetch();
$manifest = '/tmp/' . $prefix . '.json';
if ($mode === 'create') {
    if ($page || is_file($manifest)) { throw new RuntimeException('Fixture already exists.'); }
    $page = $db->table('pages')->insert(['slug' => $prefix, 'title' => $prefix, 'public' => 0, 'pages_types_id' => 1, 'pages_templates_id' => 3]);
    file_put_contents($manifest, json_encode(['id' => (int) $page->id]));
}
$id = (int) (json_decode(file_get_contents($manifest), true)['id']);
$root = realpath(__DIR__ . '/../../www');
if ($mode === 'cleanup') {
    if ($page) { (new App\Model\MediaStorage($db, $root))->deletePage($id); }
    unlink($manifest);
    echo "cleanup OK\n";
} else {
    $result = ['id' => $id, 'page' => (bool) $page];
    foreach (['media', 'pictures'] as $table) {
        $result[$table] = [];
        foreach ($db->table($table)->where('pages_id', $id) as $row) {
            $result[$table][] = ['id' => (int) $row->id, 'name' => $row->name, 'filesize' => (int) $row->filesize];
        }
        $result[$table . '_dir'] = is_dir($root . '/' . $table . '/' . $id);
    }
    echo json_encode($result) . "\n";
}
