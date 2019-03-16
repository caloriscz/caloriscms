<?php
namespace Caloriscz\Page;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class PageSlugControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($pageId): void
    {
        $page = $this->database->table('pages')->get($pageId);

        if ($this->getPresenter()->translator->getLocale() === $this->getPresenter()->translator->getDefaultLocale()) {
            $this->template->slug = '/' . $page->slug;
        } else {
            $this->template->slug = '/' . $this->getPresenter()->translator->getLocale() . '/' . $page->{'slug_' . $this->getPresenter()->translator->getLocale()};
        }

        $this->template->setFile(__DIR__ . '/PageSlugControl.latte');
        $this->template->render();
    }

}