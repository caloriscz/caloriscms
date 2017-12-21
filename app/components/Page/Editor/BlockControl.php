<?php

namespace Caloriscz\Page\Editor;

use Nette\Application\UI\Control;

class BlockControl extends Control
{
    private $htmlPurifier;

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $template->widgets = $this->database->table('pages_widgets')
            ->where('pages_id', $this->presenter->getParameter('id'))
            ->order('sorted');

        $template->setFile(__DIR__ . '/BlockControl.latte');

        $template->render();
    }

}