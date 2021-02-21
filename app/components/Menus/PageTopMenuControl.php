<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class PageTopMenuControl extends Control
{

    public $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->page = $this->database->table('pages')->get($this->presenter->getParameter('id'));

        $template->name = $this->presenter->getName();
        $template->view = $this->presenter->getView();

        $template->setFile(__DIR__ . '/PageTopMenuControl.latte');
        $template->render();
    }

}