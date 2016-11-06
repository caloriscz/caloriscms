<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class DocumentsPresenter extends BasePresenter
{
    public function renderDefault()
    {
        $this->template->folders = $this->database->table("pages")->where(array("pages_id" => 6));
    }

}
