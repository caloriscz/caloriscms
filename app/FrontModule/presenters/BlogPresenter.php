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

    public function renderDefault()
    {
        if ($this->getParameter("id")) {
            $blog = $this->database->table("pages")
                ->where(array(
                    "categories_id = ?" => $this->getParameter("id"),
                    "date_published <= ?" => date('Y-m-d H:i:s'),
                    "pages_types_id" => 2,
                ))
                ->order("date_created DESC");
        } else {
            $blog = $this->database->table("pages")
                ->where(array(
                    "public" => 1,
                    "date_published <= ?" => date('Y-m-d H:i:s'),
                    "pages_types_id" => 2,
                ))
                ->order("date_created DESC");
        }

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($blog->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));
        $this->template->blog = $blog->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->paginator = $paginator;

        $this->template->args = $this->getParameters();
    }

    public function renderDetail()
    {
        $blog = $this->database->table("pages")->get($this->getParameter("page_id"));
        $this->template->blog = $blog;
    }

}
