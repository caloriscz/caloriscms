<?php

namespace App\FrontModule\Presenters;
use Caloriscz\Page\Pages\HomepageControl;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
