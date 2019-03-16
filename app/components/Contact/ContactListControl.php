<?php
namespace Caloriscz\Contact;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class ContactListControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/ContactListControl.latte');
        $this->template->contacts = $this->database->table('contacts')->where('categories_id', 9)->order('order ASC');
        $this->template->render();
    }

}
