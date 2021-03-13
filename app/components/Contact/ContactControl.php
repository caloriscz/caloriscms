<?php

namespace Caloriscz\Contact;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class ContactControl extends Control
{
    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render($id): void
    {
        $this->template->setFile(__DIR__ . '/ContactControl.latte');
        $this->template->contact = $this->database->table('contacts')->get($id);
        $this->template->render();
    }

}
