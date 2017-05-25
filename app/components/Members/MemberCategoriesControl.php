<?php
namespace Caloriscz\Members;

use Nette\Application\UI\Control;

class MemberCategoriesControl extends Control
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
        $template->categories = $this->database->table("users_categories")->order("id");

        $template->setFile(__DIR__ . '/MemberCategoriesControl.latte');

        $template->render();
    }

}