<?php
namespace Caloriscz\Page;

use Nette\Application\UI\Control;

class PageTitleControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($page)
    {
        $this->template->page = $page;

        if ($this->getPresenter()->translator->getLocale() == $this->getPresenter()->translator->getDefaultLocale()) {
            $title = $page->title;
        } else {
            $title = $page->{'title_' . $this->getPresenter()->translator->getLocale()};
        }

        $this->template->title = $title;
        $this->template->pageId = $page->id;
        $this->template->setFile(__DIR__ . '/PageTitleControl.latte');
        $this->template->render();
    }

}