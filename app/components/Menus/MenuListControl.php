<?php

namespace Caloriscz\Menus;

use App\Forms\Menu\InsertMenuControl;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Tracy\Debugger;

class MenuListControl extends Control
{
    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentMenuInsert()
    {
        return new InsertMenuControl($this->database);
    }

    /**
     * Delete menu
     */
    public function handleDelete()
    {
        $this->database->table('menu_menus')->get($this->getPresenter()->getParameter('id'))->delete();

        $this->getPresenter()->redirect('this');
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->menu = $this->database->table('menu_menus')->order('title');
        $template->setFile(__DIR__ . '/MenuListControl.latte');
        $template->render();
    }
}