<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class GalleryPresenter extends BasePresenter
{
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

    public function renderAlbumWithDescription()
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