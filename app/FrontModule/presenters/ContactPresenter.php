<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Contact presenter.
 */
class ContactPresenter extends BasePresenter
{

    protected function createComponentHelpdesk()
    {
        $control = new \HelpdeskControl($this->database);
        return $control;
    }

    public function renderDefault()
    {
        $this->template->page = $this->database->table("pages")->get(2);
        $this->template->snippets = $this->database->table("snippets")->where("pages_id", 2)->fetchPairs('id', 'content');
    }

}
