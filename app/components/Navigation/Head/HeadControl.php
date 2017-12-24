<?php

namespace Caloriscz\Navigation\Head;

use Kdyby\Translation\Translator;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class HeadControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($slugArray)
    {
        $page = $this->getPresenter()->template->page;
        $this->template->page = $page;
        $this->template->slug = $slugArray;

        /* Choose correct language columns */
        if (count($slugArray) > 0) {
            $this->template->title = $slugArray["title"];
            $this->template->metadesc = $slugArray["metadesc"];
            $this->template->metakeys = $slugArray["metakeys"];
        } elseif ($this->getPresenter()->translator->getLocale() === $this->getPresenter()->translator->getDefaultLocale()) {
            $this->template->title = $page->title;
            $this->template->metadesc = $page->metadesc;
            $this->template->metakeys = $page->metakeys;
        } else {
            $this->template->title = $page->{'title_' . $this->presenter->translator->getLocale()};
            $this->template->metadesc = $page->{'metadesc_' . $this->presenter->translator->getLocale()};
            $this->template->metakeys = $page->{'metakeys_' . $this->presenter->translator->getLocale()};
        }

        $this->template->settings = $this->presenter->template->settings;
        $this->template->setFile(__DIR__ . '/HeadControl.latte');

        $this->template->render();
    }

}