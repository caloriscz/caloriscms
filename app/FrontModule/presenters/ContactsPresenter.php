<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Contact and visitcards
 */
class ContactsPresenter extends BasePresenter
{
    function renderDefault()
    {
        $this->template->contacts = $this->database->table("contacts")->where("categories_id", 9)->order("order ASC");
    }
}