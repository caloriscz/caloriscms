<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class SideMenuControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($id, $style = 'sidemenu')
    {
        $template = $this->getTemplate();
        
        $template->setFile(__DIR__ . '/SideMenuControl.latte');
        
        $template->id = $id;
        $template->style = $style;
        $template->categories = $this->database->table('categories')->where('parent_id', $id);
        $template->render();
    }

}
