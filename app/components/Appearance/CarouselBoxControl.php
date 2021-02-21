<?php
namespace Caloriscz\Appearance;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class CarouselBoxControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $this->template->settings = $this->presenter->template->settings;
        $this->template->carousel = $this->database->table('carousel')->where(['visible' => 1])->order('sorted');
        $this->template->setFile(__DIR__ . '/CarouselBoxControl.latte');
        $this->template->render();
    }

}