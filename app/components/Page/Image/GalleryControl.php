<?php
namespace Caloriscz\Page\Image;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class GalleryControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
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