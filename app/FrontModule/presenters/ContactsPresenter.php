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
        $this->template->contacts = $this->database->table("contacts")->order("order ASC");
    }

    function renderDetail()
    {
        $page = $this->database->table("pages")->get($this->getParameter("page_id"));
        $this->template->page = $page;
    }

}