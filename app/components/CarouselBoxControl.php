<?php

use Nette\Application\UI\Control;

class CarouselBoxControl extends Control
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
        $template->settings = $this->presenter->template->settings;

        $template->carousel = $this->database->table("appearance_carousel")->where(array(
            "visible" => 1
        ));

        $template->setFile(__DIR__ . '/CarouselBoxControl.latte');


        $template->render();
    }

}