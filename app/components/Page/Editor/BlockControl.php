<?php

namespace Caloriscz\Page\Editor;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class BlockControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render()
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