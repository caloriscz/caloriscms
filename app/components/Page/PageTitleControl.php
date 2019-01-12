<?php

namespace Caloriscz\Page;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageTitleControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($page): void
    {
        $template = $this->getTemplate();

        $template->page = $page;

        if ($page) {
            if ($this->getPresenter()->translator->getLocale() === $this->getPresenter()->translator->getDefaultLocale()) {
                $title = $page->title;
            } else {
                $title = $page->{'title_' . $this->getPresenter()->translator->getLocale()};
            }

            $template->title = $title;
            $template->pageId = $page->id;
        } else {
            $template->title = null;
            $template->pageId = '';
        }

        $template->setFile(__DIR__ . '/PageTitleControl.latte');
        $template->render();
    }

}