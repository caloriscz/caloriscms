<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class PricelistPresenter extends BasePresenter
{
    public function renderDefault()
    {
        $this->template->menu = $this->database->table("pricelist")
                ->select("pricelist.id, pricelist.categories_id, pricelist.title AS amenu, "
                        . "pricelist.description, pricelist.price, categories.title")
                ->order("categories_id, sorted DESC");
    }

    public function renderDaily()
    {
        $dayDb = $this->database->table("pricelist_dates")->where(array("day" => date("Y-m-d")));

        if ($dayDb->count() > 0) {
            $this->template->whatIsDate = date("j. n. Y");
            $this->template->whatIsDay = date("w");

            $this->template->menu = $this->database->table("pricelist_daily")
                    ->select("pricelist_daily.id, pricelist_daily.categories_id, pricelist_daily.categories_id, "
                            . "pricelist_daily.title AS amenu, pricelist_daily.price, categories.title")
                    ->where(array("pricelist_dates_id" => $dayDb->fetch()->id))
                    ->order("categories_id, amenu");
        } else {
            $this->template->menu = array();
        }
    }

}