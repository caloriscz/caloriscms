<?php

namespace Caloriscz\Navigation;

use App\Forms\Pages\SearchControl;
use Caloriscz\Menus\MenuControl;
use Caloriscz\Menus\NavbarMenuControl;
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

    protected function createComponentSearch(): SearchControl
    {
        return new SearchControl($this->database);
    }

    protected function createComponentNavbarMenu(): NavbarMenuControl
    {
        return new NavbarMenuControl($this->database);
    }

    protected function createComponentMenu(): MenuControl
    {
        return new MenuControl($this->database);
    }

    public function handleChangeLocale($locale): void
    {
        $this->presenter->translator->setLocale($locale);
        $this->presenter->redirect('this');
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->langSelected = $this->presenter->translator->getLocale();
        $template->user = $this->presenter->user;

        if (isset($this->presenter->template->member)) {
            $template->member = $this->presenter->template->member;
        }

        $template->args = $this->presenter->getParameters(true);
        $template->setFile(__DIR__ . '/NavigationControl.latte');
        $template->render();
    }
}