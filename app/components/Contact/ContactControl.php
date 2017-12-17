<?php

use Nette\Application\UI\Control;

class ContactControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($id)
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ContactControl.latte');
        $contact = $this->database->table('contacts')->get($id);

        $template->contact = $contact;

        $template->render();
    }

}
