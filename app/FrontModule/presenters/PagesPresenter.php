<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Displays pages with page_type = 0
 */
class PagesPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->page = $this->database->table("pages")->get($this->getParameter("page_id"));

    }

}
