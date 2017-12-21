<?php

namespace App\FrontModule\Presenters;

use Caloriscz\Helpdesk\HelpdeskControl;

/**
 * Contact presenter.
 */
class ContactPresenter extends BasePresenter
{
    protected function createComponentHelpdesk()
    {
        $control = new HelpdeskControl($this->database);
        return $control;
    }

    public function renderDefault()
    {
        $this->template->page = $this->database->table('pages')->get(2);
    }

}
