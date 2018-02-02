<?php

namespace App\FrontModule\Presenters;
use App\Forms\Helpdesk\HelpdeskControl;


/**
 * Contact presenter.
 */
class ContactPresenter extends BasePresenter
{
    protected function createComponentHelpdesk()
    {
        return new HelpdeskControl($this->database);
    }

    public function renderDefault()
    {
        $this->template->page = $this->database->table('pages')->get(2);
    }

}
