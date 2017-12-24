<?php
namespace Caloriscz\Members;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class MemberCategoriesControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        $template = $this->template;
        $template->categories = $this->database->table('users_categories')->order('id');

        $template->setFile(__DIR__ . '/MemberCategoriesControl.latte');

        $template->render();
    }

}