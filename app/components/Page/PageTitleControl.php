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
        $template = $this->template;

        $template->page = $page;

        if ($this->presenter->translator->getLocale() == $this->presenter->translator->getDefaultLocale()) {
            $title = $page->title;
        } else {
            $title = $page->{'title_' . $this->presenter->translator->getLocale()};
        }

        $template->title = $title;
        $template->pageId = $page->id;

        $template->setFile(__DIR__ . '/PageTitleControl.latte');

        $template->render();
    }

}