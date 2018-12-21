<?php
namespace Caloriscz\Menus;

use Nette\Application\UI\Control;
use Nette\Database\Context;

class NavbarMenuControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function render($id, $style = 'navbar')
    {
        $template = $this->getTemplate();
        $template->appDir = APP_DIR;
        $template->setFile(__DIR__ . '/NavbarMenuControl.latte');

        $template->id = $id;
        $template->style = $style;

        // load menu info from menu_menus
        $template->menu = $this->database->table('menu_menus')->get($id);

        if ($template->menu) {
            $template->class = $template->menu->class;
            $template->categories = $this->database->table('menu')->where('parent_id', $id);
        } else {

        }


        $template->render();
    }

}
