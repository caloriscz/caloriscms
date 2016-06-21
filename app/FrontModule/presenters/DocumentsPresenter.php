<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class DocumentsPresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

                $cols = array(
            "parent_id" => $this->template->settings["categories:id:mediaDocs"],
        );
        
        $this->template->documentsCat = $this->database->table("categories")->where($cols);
    }
    public function renderDefault()
    {
        if ($this->getParameter("id")) {
            $cols = array(
                "categories_id" => $this->getParameter("id"),
            );
        } else {
            $cols = array(
                "categories_id = ?" => 8,
            );
        }

        $this->template->documentId = $this->getParameter("id");

        $this->template->documents = $this->database->table("media")
                        ->where($cols)->order("name");
    }

}
