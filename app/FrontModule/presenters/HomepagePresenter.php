<?php

namespace App\FrontModule\Presenters;

use Caloriscz\Page\HomepageControl;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();
    }

    protected function createComponentHomepage()
    {
        return new HomepageControl($this->database);
    }

    public function renderDefault()
    {
        $this->template->page = $this->database->table('pages')->get(1);
    }
}
