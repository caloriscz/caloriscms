<?php
namespace Caloriscz\Menus\Admin;

use function GuzzleHttp\Promise\exception_for;
use Nette\Application\UI\Control;

class PageTopMenuControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
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