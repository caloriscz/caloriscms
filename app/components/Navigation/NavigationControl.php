<?php
namespace Caloriscz\Navigation;

use Nette\Application\UI\Control;

class NavigationControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSearch()
    {
        $control = new \Caloriscz\Page\Filters\SearchControl($this->database);
        return $control;
    }

    protected function createComponentCartList()
    {
        $control = new \Caloriscz\Cart\ListControl($this->database);
        return $control;
    }

    protected function createComponentCartBox()
    {
        $control = new \Caloriscz\Cart\BoxControl($this->database);
        return $control;
    }

    protected function createComponentNavbarMenu()
    {
        $control = new \Caloriscz\Menus\NavbarMenuControl($this->database);
        return $control;
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $template->langSelected = $this->presenter->translator->getLocale();
        $template->user = $this->presenter->user;
        $template->member = $this->presenter->template->member;
        $template->args = $this->presenter->getParameters(TRUE);

        $template->setFile(__DIR__ . '/NavigationTopControl.latte');

        $template->render();
    }

}