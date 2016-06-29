<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;

class MenuControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($id = null, $style = 'sidemenu')
    {
        $template = $this->template;

        $template->setFile(__DIR__ . '/MenuControl.latte');

        $template->active = strtok($_SERVER["REQUEST_URI"], '?');

        $template->id = $id;
        $template->style = $style;
        $template->categories = $this->database->table('menu')->where('parent_id', $id);
        $template->render();
    }

}
