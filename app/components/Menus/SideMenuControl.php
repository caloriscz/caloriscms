<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Explorer;

class SideMenuControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function render($id, $style = 'sidemenu'): void
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/SideMenuControl.latte');
        $template->id = $id;
        $template->style = $style;
        $template->categories = $this->database->table('categories')->where('parent_id', $id);
        $template->render();
    }

}
