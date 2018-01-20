<?php

/*
 * Caloris Menu
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Nette\Database\Context;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Menu
{

    /** @var Context */
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Get name of the category
     * @param $category
     * @return bool|mixed|\Nette\Database\Table\ActiveRow|\Nette\Database\Table\IRow
     */
    public function getName($category)
    {
        $categoryDb = $this->database->table('menu')->get($category);

        return $categoryDb->title;
    }

    /**
     * Get published Categories as an array
     * @return array
     */
    public function getAll()
    {
        $categoryDb = $this->database->table('menu')->order('title');
        $category = [];

        foreach ($categoryDb as $categories) {
            $category[$categories->id] = $categories->title;
        }

        return $category;
    }

    /**
     * Get published Categories as an array
     * @return aray of categories with id
     */
    public function getAllWithSubs()
    {
        $category = $this->database->table('menu')->where('parent_id', NULL)->order('title');

        foreach ($category as $categories) {
            $catsSubs = $categoryDbSub = $this->database->table('menu')->where('parent_id', $categories->id)->order('sorted, title');

            if ($catsSubs->count() > 0) {
                foreach ($catsSubs as $categoriesSub) {
                    $categorySub[$categoriesSub->id] = $categoriesSub->title;
                }

                $category[$categories->title] = $categorySub;
            } else {
                $category[$categories->id] = $categories->title;
            }
        }

        return $category;
    }

    /**
     * Get breadcrumb navigation in associative array
     * @param $id
     * @param null $arr
     * @return array|null
     */
    public function getBreadcrumb($id, $arr = NULL)
    {
        if ($id === null) {
            return array_reverse($arr, TRUE);
        } else {
            $catDb = $this->database->table('menu')->get($id);

            if ($catDb) {
                $arr[$catDb->ref('slug', 'slug_id')->title] = $catDb->title;
                return $this->getBreadcrumb($catDb->parent_id, $arr);
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
    public function getSubIds($id, $arr = NULL)
    {
        $catDb = $this->database->table('menu')->where('parent_id', $id);

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
    public function setCategory($title, $parent, $slug = null)
    {
        if (is_numeric($parent) === false) {
            $parent = null;
        }

        $this->database->table('menu')->insert([
            'title' => $title,
            'slug_id' => $slug,
            'parent_id' => $parent
        ]);

        $this->database->query('UPDATE menus SET sorted = id WHERE sorted = 0');
    }

}
