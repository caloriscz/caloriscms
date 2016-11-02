<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    protected function createComponentHomepage()
    {
        $control = new \Caloriscz\Navigation\Homepage\HomepageControl($this->database);
        return $control;
    }

    public function renderDefault()
    {
        $this->template->page = $this->database->table("pages")->get(1);
        $this->template->snippets = $this->database->table("snippets")->where("pages_id", 1)->fetchPairs('id', 'content');
    }

}
