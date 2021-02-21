<?php

namespace Caloriscz\Menus;

use App\Forms\Menu\InsertMenuControl;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Tracy\Debugger;

class MenuListControl extends Control
{
    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentMenuInsert(): InsertMenuControl
    {
        return new InsertMenuControl($this->database);
    }

    /**
     * Delete menu
     * @throws \Nette\Application\AbortException
     */
    public function handleDelete(): void
    {
        $this->database->table('menu_menus')->get($this->getPresenter()->getParameter('id'))->delete();
        $this->getPresenter()->redirect('this');
    }

    public function render(): void
    {
        $template = $this->getTemplate();
        $template->menu = $this->database->table('menu_menus')->order('title');
        $template->setFile(__DIR__ . '/MenuListControl.latte');
        $template->render();
    }
}