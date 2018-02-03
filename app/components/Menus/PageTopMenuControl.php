<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageTopMenuControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        $this->template->page = $this->database->table('pages')->get($this->getPresenter()->template->presenter->getParameter('id'));
        $this->template->contact = $this->database->table('contacts')
            ->where(['pages_id' => $this->template->page->id])->fetch();

        $this->template->setFile(__DIR__ . '/PageTopMenuControl.latte');

        $this->template->render();
    }

}