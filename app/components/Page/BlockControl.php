<?php

namespace Caloriscz\Page;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class BlockControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->widgets = $this->database->table('pages_widgets')
            ->where('pages_id', $this->presenter->getParameter('id'))
            ->order('sorted');

        $template->setFile(__DIR__ . '/BlockControl.latte');

        $template->render();
    }

}