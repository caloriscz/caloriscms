<?php

/*
 * Page
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * Cart model
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Page
{

    /** @var \Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Get page by id
     */
    function getPageById($id)
    {
        $page = $this->database->table("pages")->get($id);

        return $page;
    }

    /**
     * Get list of pages
     */
    function getPageList()
    {
        $pagesCategories = $this->database->table("pages")->where("public = 1")->order("id");

        foreach ($pagesCategories as $value) {
            $pages[$value->id] = '- ' . $value->title;
        }

        return $pages;
    }

    /**
     * Get child pages of given page
     */
    function getChildren($id, $arr = NULL)
    {
        if ($id == NULL) {
            return array_reverse($arr, TRUE);
        } else {
            $cat = $this->database->table("pages")->where("pages_id", $id);

            if ($cat->count() > 0) {
                foreach ($cat as $item) {
                    $arr[] = $item->id;
                    $arrs[] = $item->id;
                }

                return $this->getChildren($arrs, $arr);
            } else {
                return $arr;
            }
        }
    }

}
