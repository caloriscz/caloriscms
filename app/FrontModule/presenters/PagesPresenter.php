<?php

namespace App\FrontModule\Presenters;

use Caloriscz\Blog\BlogListControl;

/**
 * Displays pages with page_type = 0
 */
class PagesPresenter extends BasePresenter
{
    /**
     * @return BlogListControl
     */
    protected function createComponentBlogList(): BlogListControl
    {
        return new BlogListControl($this->database);
    }

    protected function startup()
    {
        parent::startup();

        $this->template->page = $this->database->table('pages')->get($this->getParameter('page_id'));

    }

}
