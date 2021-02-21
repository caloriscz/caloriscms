<?php
namespace Caloriscz\Navigation;

use Caloriscz\Menus\MenuControl;
use Caloriscz\Social\FacebookControl;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class FooterControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentSocialFacebook(): FacebookControl
    {
        return new FacebookControl();
    }

    protected function createComponentMenu(): MenuControl
    {
        return new MenuControl($this->database);
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/' . $template->settings['navigation_footer_template'] . 'Control.latte');
        $template->render();
    }

}
