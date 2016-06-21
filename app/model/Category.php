<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Category
{

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Get name of the category
     */
    function getName($category)
    {
        $categoryDb = $this->database->table("categories")->get($category);

        return $categoryDb->title;
    }

    /**
     * Get published Categories as an array
     * @return aray of categories with id
     */
    function getPublished()
    {
        foreach ($this->database->table("store")->group("category")->having("publish = 1") as $categories) {
            $category[$categories->category] = $this->getName($categories->category);
        }

        return $category;
    }

    /**
     * Get published Categories as an array
     * @return aray of categories with id
     */
    function getAll()
    {
        foreach ($categoryDb = $this->database->table("categories")->order("title") as $categories) {
            $category[$categories->id] = $categories->title;
        }

        return $category;
    }

    /**
     * Get published Categories as an array
     * @return aray of categories with id
     */
    function getAllWithSubs()
    {
        foreach ($categoryDb = $this->database->table("categories")->where('parent_id', NULL)->order("title") as $categories) {
            $catsSubs = $categoryDbSub = $this->database->table("categories")->where('parent_id', $categories->id)->order("sorted, title");

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
     */
    function getBreadcrumb($id, $arr = NULL)
    {
        if ($id == NULL) {
            return array_reverse($arr, TRUE);
        } else {
            $catDb = $this->database->table("categories")->get($id);

            if ($catDb) {
                $arr[$catDb->ref('slug', 'slug_id')->title] = $catDb->title;
                return $this->getBreadcrumb($catDb->parent_id, $arr);
            } else {
                return $arr;
            }
        }
    }

    /**
     * Get breadcrumb navigation in associative array
     */
    function getPageBreadcrumb($id, $arr = NULL)
    {
        if ($id == NULL) {
            return array_reverse($arr, TRUE);
        } else {
            $catDb = $this->database->table("pages")->get($id);

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
     */
    function getSubIds($id, $arr = NULL)
    {
        $catDb = $this->database->table("categories")->where("parent_id", $id);

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
     */
    function setCategory($title, $parent, $slug = null)
    {
        if (is_numeric($parent) == false) {
            $parent = null;
        }

        $this->database->table("categories")->insert(array(
            "title" => $title,
            "slug_id" => $slug,
            "parent_id" => $parent,
        ));

        $this->database->query("UPDATE categories SET sorted = id WHERE sorted = 0");
    }

}
