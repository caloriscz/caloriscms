<?php
namespace Caloriscz\Page\Related;

use Nette\Application\UI\Control;

class PageListControl extends Control
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
        $template->storeRelated = $page->related('pages_related', 'pages_id');
        $template->database = $this->database;
        $template->setFile(__DIR__ . '/PageListControl.latte');

        $template->render();
    }

}
