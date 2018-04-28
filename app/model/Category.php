<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;
use Nette\Database\Context;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Category
{

    /** @var Context */
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }


    /**
     * Get breadcrumb navigation in associative array
     * @param $id
     * @param null $arr
     * @return array|null
     */
    public function getPageBreadcrumb($id, $arr = null): ?array
    {
        if ($id === null) {
            return array_reverse($arr, true);
        } else {
            $catDb = $this->database->table('pages')->get($id);

            if ($catDb) {
                $arr[$catDb->id] = $catDb->title;
                return $this->getPageBreadcrumb($catDb->pages_id, $arr);
            } else {
                return $arr;
            }
        }
    }

    /**
     * Get breadcrumb ids
     * @param $id
     * @param null $arr
     * @return array|null
     */
    public function getSubIds($id, $arr = null): ?array
    {
        $catDb = $this->database->table('categories')->where('parent_id', $id);

        if (!is_array($arr)) {
            $arr[] = (int)$id;
        }

        if ($catDb->count() > 0) {
            foreach ($catDb as $value) {
                $arrs[] = $value->id;
                $arr[] = $value->id;
            }

            return $this->getSubIds($arrs, $arr);
        } else {
            asort($arr);
            return $arr;
        }
    }

    /**
     * Create new category
     * @param $title
     * @param $parent
     * @param null $slug
     */
    public function setCategory($title, $parent, $slug = null): void
    {
        if (is_numeric($parent) === false) {
            $parent = null;
        }

        $this->database->table('contacts_categories')->insert([
            'title' => $title,
            'parent_id' => $parent
        ]);

        $this->database->query('UPDATE contacts_categories SET sorted = id WHERE sorted = 0');
    }

}
