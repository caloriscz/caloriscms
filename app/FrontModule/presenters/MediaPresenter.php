<?php

namespace App\FrontModule\Presenters;

use Nette\Utils\Paginator;

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
        $arr = [
            'pages_types_id' => 6
        ];

        $this->template->gallery = $this->database->table('pages')->where($arr)->order('title');
    }

    public function renderAlbumWithDescription()
    {
        $cols = ['pages_id' => $this->getParameter('page_id')];

        $gallery = $this->database->table('pictures')->where($cols)->order('name');


        $paginator = new Paginator();
        $paginator->setItemCount($gallery->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter('page'));

        $this->template->paginator = $paginator;
        $this->template->gallery = $gallery->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->args = $this->getParameters();
    }

}
