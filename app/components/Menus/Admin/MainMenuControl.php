<?php

namespace Caloriscz\Menus\Admin;

use Nette\Application\UI\Control;

class MainMenuControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    public function render()
    {
        $template = $this->template;

        $template->member = $this->presenter->template->member;
        $template->addons = $this->database->table("addons");

        $template->setFile(__DIR__ . '/MainMenuControl.latte');

        $template->render();
    }

}
