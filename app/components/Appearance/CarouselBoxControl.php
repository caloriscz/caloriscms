<?php
namespace Caloriscz\Appearance;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class CarouselBoxControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();

        $this->database = $database;
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        $template->carousel = $this->database->table("carousel")->where(array(
            "visible" => 1
        ))->order("sorted");

        $template->setFile(__DIR__ . '/CarouselBoxControl.latte');


        $template->render();
    }

}