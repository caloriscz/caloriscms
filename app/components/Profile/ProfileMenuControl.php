<?php

namespace Caloriscz\Profile;

use Nette\Application\UI\Control;

class ProfileMenuControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentPageSlug()
    {
        $control = new \Caloriscz\Page\PageSlugControl($this->database);
        return $control;
    }

    public function render()
    {
        $template = $this->template;
        $template->pageId = $this->presenter->getParameter("page_id");

        $template->setFile(__DIR__ . '/ProfileMenuControl.latte');

        $template->render();
    }

}
