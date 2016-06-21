<?php

use Nette\Application\UI\Control;

class AddressLabelControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($address)
    {
        $template = $this->template;
        $template->address = $address;
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/AddressLabelControl.latte');

        $template->render();
    }

}
