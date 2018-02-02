<?php
namespace Caloriscz\Page\Pages;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageItemControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($item)
    {
        $template = $this->getTemplate();
        $template->isLoggedIn = $this->presenter->template->isLoggedIn;
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/PageItemControl.latte');
        $template->item = $item;
        $template->render();
    }

}
