<?php

use Nette\Application\UI\Control;

class ContactControl extends Control
{
    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function render($id)
    {
        $this->template->setFile(__DIR__ . '/ContactControl.latte');
        $this->template->contact = $this->database->table('contacts')->get($id);
        $this->template->render();
    }

}
