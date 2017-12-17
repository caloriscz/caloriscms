<?php
namespace Caloriscz\Contact;

use Nette\Application\UI\Control;

class ContactListControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ContactListControl.latte');

        $template->contacts = $this->database->table('contacts')->where('categories_id', 9)->order('order ASC');

        $template->render();
    }

}
