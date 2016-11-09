<?php
namespace Caloriscz\Page;

use Nette\Application\UI\Control;

class PageSlugControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($pageId)
    {
        $template = $this->template;
        $page = $this->database->table("pages")->get($pageId);

        if ($this->presenter->translator->getLocale() == $this->presenter->translator->getDefaultLocale()) {
            $template->slug = "/" . $page->slug;
        } else {
            $template->slug = "/" . $this->presenter->translator->getLocale() . '/' . $page->{'slug_' . $this->presenter->translator->getLocale()};
        }

        $template->setFile(__DIR__ . '/PageSlugControl.latte');

        $template->render();
    }

}