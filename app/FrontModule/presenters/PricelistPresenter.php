<?php
namespace App\FrontModule\Presenters;

/**
 * Pricelist presenter.
 */
class PricelistPresenter extends BasePresenter
{
    public function renderDefault(): void
    {
        $menuId = $this->getParameter('menu');

        if (empty($menuId)) {
            $menuId = 1;
        }

        $this->template->menuId = $menuId;
        $this->template->lists = $this->database->table('pricelist_lists');
        $this->template->listActive = $this->database->table('pricelist_lists')->get($menuId);

        $currencyDb = $this->database->table('currencies')->get($this->template->settings['site:currency']);
        $this->template->currency = $currencyDb;

        $this->template->menu = $this->database->table('pricelist')
            ->select('pricelist.id, pricelist.pricelist_categories_id, pricelist.title AS amenu, '
                . 'pricelist.description, pricelist.price, pricelist_categories.title, pricelist_categories.sorted')
            ->where(['pricelist_lists_id' => $menuId])
            ->order('pricelist_categories.sorted, pricelist.sorted DESC');
    }

    public function renderDaily(): void
    {
        $dayDb = $this->database->table('pricelist_dates')->where(['day' => date('Y-m-d')]);

        if ($dayDb->count() > 0) {
            $this->template->whatIsDate = date('j. n. Y');
            $this->template->whatIsDay = date('w');

            $this->template->menu = $this->database->table('pricelist_daily')
                ->select('pricelist_daily.id, pricelist_daily.pricelist_categories_id, pricelist_daily.pricelist_categories_id, '
                    . 'pricelist_daily.title AS amenu, pricelist_daily.price, pricelist_categories.title')
                ->where(['pricelist_dates_id' => $dayDb->fetch()->id])
                ->order('pricelist_categories_id, amenu');
        } else {
            $this->template->menu = [];
        }
    }

}
