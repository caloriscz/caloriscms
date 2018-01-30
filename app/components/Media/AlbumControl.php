<?php
namespace Caloriscz\Media;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class AlbumControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($itemCat)
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $template->itemCat = $itemCat;
        $template->setFile(__DIR__ . '/AlbumControl.latte');

        $template->render();
    }

}
