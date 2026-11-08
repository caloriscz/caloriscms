<?php

namespace Caloriscz\Navigation;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class AdminBarControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Specifies type of category: store, media, blog, menu etc. Some categories have special needs
     */
    public function render(): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->enabled = false;
        $template->page = false;

        $template->roleCheck = $this->presenter->template->memberRole;

        if ($this->presenter->template->isLoggedIn && $template->roleCheck
            && (int) $template->roleCheck->sign === 1 && $template->settings['site:admin:adminBarEnabled']
            && $this->presenter->template->member->adminbar_enabled) {
            $template->enabled = true;
        }

        if ($this->presenter->template->page) {
            $template->pageId = $this->presenter->template->page->id;
        } else {
            $template->pageId = null;
        }

        $template->presenterName = $this->presenter->getName();
        $template->presenterView = $this->presenter->getView();
        $template->slug = $this->presenter->getParameter('slug');
        $template->setFile(__DIR__ . '/AdminBarControl.latte');

        $template->render();
    }

}
