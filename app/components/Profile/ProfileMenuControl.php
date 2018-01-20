<?php

namespace Caloriscz\Profile;

use Caloriscz\Page\PageSlugControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class ProfileMenuControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function createComponentPageSlug()
    {
        $control = new PageSlugControl($this->database);
        return $control;
    }

    public function render()
    {
        $this->template->pageId = $this->presenter->getParameter("page_id");
        $this->template->setFile(__DIR__ . '/ProfileMenuControl.latte');
        $this->template->render();
    }

}
