<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Displays pages with page_type = 0
 */
class PagesPresenter extends BasePresenter
{
    protected function createComponentBlogList()
    {
        $control = new \Caloriscz\Blog\BlogListControl($this->database);
        return $control;
    }

    protected function startup()
    {
        parent::startup();

        $this->template->page = $this->database->table('pages')->get($this->getParameter('page_id'));

    }

}
