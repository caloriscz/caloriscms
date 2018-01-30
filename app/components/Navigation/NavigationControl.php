<?php

namespace Caloriscz\Navigation;

use Caloriscz\Menus\NavbarMenuControl;
use Caloriscz\Page\Filters\SearchControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;

class NavigationControl extends Control
{
    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSearch()
    {
        $control = new SearchControl($this->database);
        return $control;
    }

    protected function createComponentNavbarMenu()
    {
        $control = new NavbarMenuControl($this->database);
        return $control;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->langSelected = $this->presenter->translator->getLocale();
        $template->user = $this->presenter->user;

        if (isset($this->presenter->template->member)) {
            $template->member = $this->presenter->template->member;
        }

        $template->args = $this->presenter->getParameters(TRUE);
        $template->setFile(__DIR__ . '/NavigationControl.latte');
        $template->render();
    }
}