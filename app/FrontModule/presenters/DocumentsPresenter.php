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

    public function renderFolder()
    {
        $this->template->album = $this->database->table("pages")
            ->get($this->getParameter("page_id"));

        $cols = array(
            "pages_id" => $this->getParameter("page_id"),
        );

        $this->template->gallery = $this->database->table("media")
            ->where($cols)->order("name");
    }

}
