<?php

namespace App\ApiModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class SitemapPresenter extends BasePresenter
{

    function renderDefault()
    {
        $this->template->pages = $this->database->table("pages")->where(array(
            "public" => 1
        ));
    }

}
