<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function createComponentHomepage()
    {
        $control = new \Caloriscz\Page\Pages\Homepage\HomepageControl($this->database);
        return $control;
    }

    public function renderDefault()
    {
        $this->template->page = $this->database->table("pages")->get(1);
    }

}
