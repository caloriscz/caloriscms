<?php
namespace Caloriscz\Navigation\Footer;

use Nette\Application\UI\Control;

class FooterControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSocialFacebook()
    {
        $control = new \Caloriscz\Social\FacebookControl;
        return $control;
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/FooterControl.latte');

        $template->render();
    }

}
