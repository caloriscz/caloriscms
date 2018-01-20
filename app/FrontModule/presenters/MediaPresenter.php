<?php

namespace App\FrontModule\Presenters;

/**
 * Images and files presenter.
 */
class MediaPresenter extends BasePresenter
{
    public function renderFolder()
    {
        $this->template->album = $this->database->table('pages')->get($this->getParameter('page_id'));

        $cols = ['pages_id' => $this->getParameter('page_id')];

        $this->template->gallery = $this->database->table('media')->where($cols)->order('name');
    }

    public function renderAlbum()
    {
        $this->template->album = $this->database->table('pages')->get($this->getParameter('page_id'));

        $cols = ['pages_id' => $this->getParameter('page_id')];

        $this->template->gallery = $this->database->table('media')->where($cols)->order('name');
    }

    public function renderAlbumWithDescription()
    {
        $this->template->album = $this->database->table('pages')->get($this->getParameter('page_id'));

        $cols = ['pages_id' => $this->getParameter('page_id')];

        $this->template->gallery = $this->database->table('media')->where($cols)->order('name');
    }

}
