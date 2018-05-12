<?php

namespace Caloriscz\Lang;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class LangListControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render(): void
    {
        $getParams = $this->getParameters();
        unset($getParams['page']);
        $this->template->args = $getParams;

        $this->template->setFile(__DIR__ . '/LangListControl.latte');

        $this->template->idActive = $this->presenter->getParameter('id');
        $this->template->menu = $this->database->table('lang_list')->order('title');
        $this->template->render();
    }

}