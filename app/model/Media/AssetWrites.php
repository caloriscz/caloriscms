<?php
declare(strict_types=1);
namespace App\Model;

use Nette\Application\BadRequestException;
use Nette\Database\Explorer;

/** Ownership decisions for the existing carousel, appearance and link forms. */
final class AssetWrites
{
    private Explorer $db;
    private AssetStorage $storage;

    public function __construct(Explorer $db, string $root)
    {
        $this->db = $db;
        $this->storage = new AssetStorage($db, $root);
    }

    public function carousel(?int $id, array $values, array $file): void
    {
        $this->storage->run(function ($assets) use ($id, $values, $file): void {
            $row = $id === null ? null : $this->row('carousel', $id);
            $old = $row ? $row->image : null;
            if (AssetStorage::hasUpload($file)) {
                $values['image'] = bin2hex(random_bytes(12)) . '.' . pathinfo(AssetStorage::imageName($file), PATHINFO_EXTENSION);
                $assets->image($file, 'images/carousel/' . $values['image']);
                if ($old && $this->db->table('carousel')->where('image', $old)->count('*') === 1) {
                    $this->removeStored($assets, 'images/carousel/', $old);
                }
            } elseif (!$row) {
                // carousel.image is NOT NULL without a default; a slide needs an image.
                throw new BadRequestException('Vyberte obrázek nové položky karuselu.', 400);
            }
            if ($row) { $row->update($values); }
            else {
                // carousel.sorted is NOT NULL without a default: append at the end.
                $values['sorted'] = (int) $this->db->table('carousel')->max('sorted') + 2;
                $this->db->table('carousel')->insert($values);
            }
        });
    }

    public function deleteCarousel(int $id): void
    {
        $this->storage->run(function ($assets) use ($id): void {
            $row = $this->row('carousel', $id);
            if ($row->image && $this->db->table('carousel')->where('image', $row->image)->count('*') === 1) {
                $this->removeStored($assets, 'images/carousel/', $row->image);
            }
            $row->delete();
        });
    }

    public function setting(int $id, array $file): void
    {
        $this->storage->run(function ($assets) use ($id, $file): void {
            $row = $this->row('settings', $id);
            if ($row->type !== 'local_path') { throw new BadRequestException('Toto nastavení není obrázek.', 400); }
            $name = bin2hex(random_bytes(12)) . '.' . pathinfo(AssetStorage::imageName($file), PATHINFO_EXTENSION);
            $assets->image($file, 'images/paths/' . $name);
            if ($row->setvalue && $this->db->table('settings')->where(['type' => 'local_path', 'setvalue' => $row->setvalue])->count('*') === 1) {
                $this->removeStored($assets, 'images/paths/', $row->setvalue);
            }
            $row->update(['setvalue' => $name]);
        });
    }

    /**
     * Remove the file a row used to own. A stored name that is not a plain
     * basename cannot be addressed safely: keep that file, log it and still
     * finish the row operation instead of blocking it.
     */
    private function removeStored(AssetStorage $assets, string $directory, string $name): void
    {
        if (!AssetStorage::isRemovableName($name)) {
            \Tracy\Debugger::log('Asset left in place; unusable stored name in ' . $directory, \Tracy\Debugger::WARNING);
            return;
        }
        $assets->remove($directory . $name);
    }

    public function link(int $id, array $values, array $file): void
    {
        $this->storage->run(function ($assets) use ($id, $values, $file): void {
            $row = $this->row('links', $id);
            if (AssetStorage::hasUpload($file)) { $assets->image($file, 'links-media/link-' . $id . '.jpg', true); }
            $row->update($values);
        });
    }

    public function deleteLink(int $id, bool $imageOnly = false): void
    {
        $this->storage->run(function ($assets) use ($id, $imageOnly): void {
            $row = $this->row('links', $id);
            $assets->remove('links-media/link-' . $id . '.jpg');
            if (!$imageOnly) { $row->delete(); }
        });
    }

    private function row(string $table, int $id)
    {
        $row = $id > 0 ? $this->db->table($table)->get($id) : null;
        if (!$row) { throw new BadRequestException('Položka neexistuje.', 404); }
        return $row;
    }

    public static function menuIds(Explorer $db, array $ids): array
    {
        $seen = [];
        while ($ids) {
            $id = (int) array_pop($ids);
            if (isset($seen[$id])) { continue; }
            $seen[$id] = $id;
            foreach ($db->table('menu')->where('parent_id', $id) as $child) { $ids[] = (int) $child->id; }
        }
        return array_values($seen);
    }

    public function deleteMenu(int $id, bool $group = false): void
    {
        $this->storage->run(function ($assets) use ($id, $group): void {
            $row = $this->row($group ? 'menu_menus' : 'menu', $id);
            $ids = $group ? array_values($this->db->table('menu')->where('menu_menus_id', $id)->fetchPairs('id', 'id')) : [$id];
            foreach (self::menuIds($this->db, $ids) as $menuId) {
                foreach (['', '_h', '_a', '_ah'] as $suffix) { $assets->remove('images/menu/' . $menuId . $suffix . '.png'); }
            }
            $row->delete();
        });
    }
}
