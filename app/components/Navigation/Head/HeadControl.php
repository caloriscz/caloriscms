<?php

namespace Caloriscz\Navigation\Head;

use Kdyby\Translation\Translator;
use Nette\Application\UI\Control;

class HeadControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($slugArray)
    {
        $template = $this->template;

        $page = $this->presenter->template->page;
        $template->slug = $slugArray;

        /* Choose correct language columns */
        if (count($slugArray) > 0) {
            $template->title = $slugArray["title"];
            $template->metadesc = $slugArray["metadesc"];
            $template->metakeys = $slugArray["metakeys"];
        } elseif ($this->presenter->translator->getLocale() == $this->presenter->translator->getDefaultLocale()) {
            $template->title = $page->title;
            $template->metadesc = $page->metadesc;
            $template->metakeys = $page->metakeys;
        } else {
            $template->title = $page->{'title_' . $this->presenter->translator->getLocale()};
            $template->metadesc = $page->{'metadesc_' . $this->presenter->translator->getLocale()};
            $template->metakeys = $page->{'metakeys_' . $this->presenter->translator->getLocale()};
        }

        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/HeadControl.latte');

        $template->render();
    }

}