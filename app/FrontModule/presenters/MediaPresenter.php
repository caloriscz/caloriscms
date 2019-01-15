<?php

namespace App\FrontModule\Presenters;

use Caloriscz\Media\PageGalleryControl;
use Nette\Utils\Paginator;

/**
 * Images and files presenter.
 */
class MediaPresenter extends BasePresenter
{

    public function createComponentPageGallery(): PageGalleryControl
    {
        return new PageGalleryControl($this->database);
    }

    public function renderAlbum(): void
    {
        $arr = [
            'pages_types_id' => 6,
            'public' => 1
        ];

        $gallery = $this->database->table('pages')->where($arr);

        $paginator = new Paginator();
        $paginator->setItemCount($gallery->count('*'));
        $paginator->setItemsPerPage(10);
        $paginator->setPage($this->getParameter('page'));

        $this->template->paginator = $paginator;
        $this->template->gallery = $gallery->order('title')->limit($paginator->getLength(), $paginator->getOffset());
    }

    public function renderAlbumWithDescription(): void
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

    public function renderFolder(): void
    {
        $arr = [
            'pages_types_id' => 8,
            'public' => 1
        ];

        $gallery = $this->database->table('pages')->where($arr);

        $paginator = new Paginator();
        $paginator->setItemCount($gallery->count('*'));
        $paginator->setItemsPerPage(10);
        $paginator->setPage($this->getParameter('page'));

        $this->template->paginator = $paginator;
        $this->template->gallery = $gallery->order('title')->limit($paginator->getLength(), $paginator->getOffset());
    }

    public function renderFolderList(): void
    {
        $cols = ['pages_id' => $this->getParameter('page_id')];

        $gallery = $this->database->table('media')->where($cols)->order('name');


        $paginator = new Paginator();
        $paginator->setItemCount($gallery->count('*'));
        $paginator->setItemsPerPage(20);
        $paginator->setPage($this->getParameter('page'));

        $this->template->paginator = $paginator;
        $this->template->gallery = $gallery->limit($paginator->getLength(), $paginator->getOffset());

        $this->template->args = $this->getParameters();
    }
}
