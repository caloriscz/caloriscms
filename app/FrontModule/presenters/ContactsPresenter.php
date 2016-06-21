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
        $detail = substr($this->getParameter("id"), 0, strpos($this->getParameter("id"), '-'));

        $this->template->contact = $this->database->table("contacts")
                ->get($detail);
    }

}
