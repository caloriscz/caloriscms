<?php

namespace Caloriscz\Page;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class PageGalleryControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * @param int $page page id
     * @param int $number If numbe set, result will limit number of images shown
     */
    public function render(int $page, int $number = null): void
    {
        $template = $this->getTemplate();

        $template->page = $page;

        if ($number !== null) {
            $pictures = $this->database->table('pictures')->where('pages_id', $page->id)->limit($number);
        } else {
            $pictures = $this->database->table('pictures')->where('pages_id', $page->id);
        }


        $template->pictures = $pictures;

        $template->setFile(__DIR__ . '/PageGalleryControl.latte');
        $template->render();
    }

}