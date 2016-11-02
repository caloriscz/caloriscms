<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class BlogPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->categories = $this->database->table("categories");
    }
    
    protected function createComponentBlogList()
    {
        $control = new \Caloriscz\Blog\BlogListControl($this->database);
        return $control;
    }

    public function renderDefault()
    {
        $this->template->page = $this->database->table("pages")->get(3);
        $this->template->snippets = $this->database->table("snippets")->where("pages_id", 3)->fetchPairs('id', 'content');
    }

    public function renderDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("page_id"));
    }

}
