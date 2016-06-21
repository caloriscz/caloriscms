<?php

namespace App\ApiModule\Presenters;

use Nette,
    App\Model;

/**
 * Blog presenter.
 */
class CataloguePresenter extends BasePresenter
{

    function renderDefault()
    {
        $this->template->products = $this->database->table("store")
                ->where("title LIKE ?", '%' . $this->getParameter('src'). '%')
                ->order("title");
    }

}
