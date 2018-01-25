<?php
namespace Caloriscz\Appearance;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class CarouselBoxControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();

        $this->database = $database;
    }

    public function render()
    {
        $this->template->settings = $this->presenter->template->settings;
        $this->template->carousel = $this->database->table('carousel')->where(['visible' => 1])->order('sorted');
        $this->template->setFile(__DIR__ . '/CarouselBoxControl.latte');
        $this->template->render();
    }

}