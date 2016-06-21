<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class GalleryPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();
    }

    public function renderDefault()
    {
        if ($this->getParameter("id")) {
            $mediaId = $this->getParameter('id');
        } else {
            $mediaId = null;
        }

        $cols = array(
            "file_type" => 1,
            "pages_id" => $mediaId,
        );

        $this->template->galleryId = $this->getParameter("id");
        $gallery = $this->database->table("media")
            ->where($cols);

        $paginator = new \Nette\Utils\Paginator;
        $paginator->setItemCount($gallery->count());
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter("page"));

        $this->template->paginator = $paginator;
        $this->template->galleries = $gallery->order("title")->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->args = $this->getParameters(TRUE);
    }

    public function renderAlbum()
    {
        $this->template->album = $this->database->table("pages")
            ->get($this->getParameter("page_id"));

        $cols = array(
            "pages_id" => $this->getParameter("page_id"),
        );

        $this->template->gallery = $this->database->table("media")
            ->where($cols)->order("name");
    }

}