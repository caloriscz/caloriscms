<?php

namespace Caloriscz\Page;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageGalleryControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param $page page id
     * @param int $number If numbe set, result will limit number of images shown
     */
    public function render($page, int $number = null): void
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