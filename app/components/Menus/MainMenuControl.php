<?php

namespace Caloriscz\Menus\Admin;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class MainMenuControl extends Control
{
    /** @var Explorer @inject */
    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $this->template->member = $this->getPresenter()->template->member;
        $this->template->settings = $this->getPresenter()->template->settings;
        $this->template->pageTypes = $this->database->table('pages_types');

        $this->template->setFile(__DIR__ . '/MainMenuControl.latte');
        $this->template->render();
    }

}
