<?php

/*
 * Page
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */
namespace App\Model;

use Nette\Database\Context;

/**
 * Cart model
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Page
{

    /** @var Context */
    public $database;
    public $user;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Get page by id
     * @param $id
     * @return bool|mixed|\Nette\Database\Table\ActiveRow|\Nette\Database\Table\IRow
     */
    public function getPageById($id)
    {
        return $this->database->table('pages')->get($id);
    }

    /**
     * Get list of pages
     */
    public function getPageList()
    {
        $pagesCategories = $this->database->table('pages')->where('public = 1')->order('id');

        foreach ($pagesCategories as $value) {
            $pages[$value->id] = '- ' . $value->title;
        }

        return $pages;
    }

    /**
     * Get child pages of given page
     * @param $id
     * @param null $arr
     * @return array|null
     */
    public function getChildren($id, $arr = null)
    {
        if ($id === null) {
            return array_reverse($arr, true);
        } else {
            $cat = $this->database->table('pages')->where('pages_id', $id);

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
