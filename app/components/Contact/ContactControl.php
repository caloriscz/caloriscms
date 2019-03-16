<?php

use Nette\Application\UI\Control;
use Nette\Database\Context;

class ContactControl extends Control
{
    /** @var Context */
    public $database;

    /**
     * ContactControl constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function render($id): void
    {
        $this->template->setFile(__DIR__ . '/ContactControl.latte');
        $this->template->contact = $this->database->table('contacts')->get($id);
        $this->template->render();
    }

}
