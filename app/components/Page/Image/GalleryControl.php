<?php
namespace Caloriscz\Page\Image;

use Nette\Application\UI\Control;

class GalleryControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($page)
    {
        $template = $this->template;
        $template->page = $page;
        $template->setFile(__DIR__ . '/GalleryControl.latte');

        $template->render();
    }

}