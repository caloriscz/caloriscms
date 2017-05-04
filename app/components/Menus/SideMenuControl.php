<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;

class SideMenuControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($id, $style = 'sidemenu')
    {
        $template = $this->template;
        
        $template->setFile(__DIR__ . '/SideMenuControl.latte');
        
        $template->id = $id;
        $template->style = $style;
        $template->categories = $this->database->table('categories')->where('parent_id', $id);
        $template->render();
    }

}
