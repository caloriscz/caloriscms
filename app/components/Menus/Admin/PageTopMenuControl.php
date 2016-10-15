<?php
namespace Caloriscz\Menus\Admin;

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
        $template = $this->template;

        $template->page = $this->database->table("pages")->get($this->presenter->template->presenter->getParameter("id"));

        $template->contact = $this->database->table("contacts")
            ->where(array("pages_id" => $template->page->id))->fetch();
        $template->user = $this->database->table("users")->get($template->contact->users_id);

        $template->setFile(__DIR__ . '/PageTopMenuControl.latte');

        $template->render();
    }

}