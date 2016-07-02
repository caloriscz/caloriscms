<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class EventsPresenter extends BasePresenter
{

    public function renderDefault()
    {
        $blog = $this->database->table("events")
            ->where("pages.public = 1 AND pages.pages_types_id = 3 AND date_event >= NOW()")
            ->order("date_event");

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($blog->count("*"));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));
        $this->template->board = $blog->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->paginator = $paginator;

        $this->template->blog = $blog;
        $this->template->args = $this->getParameters();
    }

    public function renderDetail()
    {
        $events = $this->database->table("events")->where(array("pages.slug" => $this->getParameter("slug")))->fetch();

        $this->template->events = $events;
    }

}