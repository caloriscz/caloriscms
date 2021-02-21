<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;
use Nette\Database\Explorer;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Category
{

    private Explorer $database;

    public function __construct(Explorer $database)
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
        }

        $catDb = $this->database->table('pages')->get($id);

        if ($catDb) {
            $arr[$catDb->id] = $catDb->title;
            return $this->getPageBreadcrumb($catDb->pages_id, $arr);
        }

        return $arr;
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
    }

}
