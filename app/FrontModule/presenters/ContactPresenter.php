<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Contact presenter.
 */
class ContactPresenter extends BasePresenter
{

    function renderDefault()
    {
        $this->template->contacts = $this->database->table("contacts")
            ->where(array("categories_id" => 45))->order("id");
			
			        $this->template->snippets = $this->database->table("snippets")->where("pages_id", 2)->fetchPairs('id', 'content');
    }

}
