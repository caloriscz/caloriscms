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

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Get list of categories
     */
    function getCategoryList($id)
    {
        $pagesCategories = $this->database->table("categories")
                ->where("parent_id", $id)
                ->order("title")->fetchPairs('id', 'title');

        return $pagesCategories;
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
     * Get list of categories
     */
    function getPageList()
    {
        $pagesCategories = $this->database->table("pages")->where("public = 1")->order("id");

        foreach ($pagesCategories as $value) {
            $pages[$value->id] = '- ' . $value->title;
        }

        return $pages;
    }

}
