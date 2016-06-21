<?php

use Nette\Application\UI\Control;

class LangSelectorControl extends Control
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
        $template->id = $this->presenter->getParameter('id');

        $template->languages = $this->database->table("languages")->order("id");
        $template->langSelected = $this->presenter->getParameter("l");

        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/LangSelectorControl.latte');


        $template->render();
    }

}
