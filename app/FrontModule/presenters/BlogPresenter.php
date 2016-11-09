<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class BlogPresenter extends BasePresenter
{
    protected function createComponentBlogList()
    {
        $control = new \Caloriscz\Blog\BlogListControl($this->database);
        return $control;
    }

    public function renderDefault()
    {
        $this->template->page = $this->database->table("pages")->get(3);
    }

}
