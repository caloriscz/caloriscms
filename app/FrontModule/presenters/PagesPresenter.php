<?php

namespace App\FrontModule\Presenters;

use App\Model\Filter;
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

    /**
     * Search page
     */
    public function renderSearch()
    {
        $filter = new Filter($this->database);
        $filter->setText($this->getParameter("s"));
        $filter->setOrder($this->getParameter("o"));
        $filter->setOptions($this->template->settings);

        $assembleSQL = $filter->assemble();

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($assembleSQL->count("*"));
        $paginator->setItemsPerPage(5);
        $paginator->setPage($this->getParameter("page"));

        $this->template->categoryArr = $this->getParameters();
        $this->template->search = $assembleSQL->order("pages.id");
        $this->template->paginator = $paginator;
        $this->template->productsArr = $assembleSQL->limit($paginator->getLength(), $paginator->getOffset());

    }

}
