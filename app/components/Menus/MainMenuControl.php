<?php

namespace Caloriscz\Menus\Admin;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class MainMenuControl extends Control
{
    /** @var Context @inject */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        if (isset($this->getPresenter()->template->member)) {
            $this->template->member = $this->getPresenter()->template->member;
        }

        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->pageTypes = $this->database->table('pages_types');

        $this->template->setFile(__DIR__ . '/MainMenuControl.latte');
        $this->template->render();
    }

}
