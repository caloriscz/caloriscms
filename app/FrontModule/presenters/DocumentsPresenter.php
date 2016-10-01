<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class DocumentsPresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        $cols = array(
            "pages_id" => 5,
        );

        $this->template->documentsCat = $this->database->table("pages")->where($cols);
    }

    public function renderDefault()
    {
        $cols = array(
            "file_type" => 1,
            "pages_id" => $this->getParameter("page_id"),
        );

        $this->template->galleryId = $this->getParameter("id");
        $docs = $this->database->table("media")
            ->where($cols);

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($docs->count());
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->documentId = $this->getParameter("id");

        $this->template->documents = $docs;
    }

}
