<?php

namespace App\AdminModule\Presenters;

use App\Forms\Menu\EditMenuControl;
use App\Forms\Menu\InsertMenuControl;
use App\Forms\Menu\MenuMenusEditControl;
use App\Forms\Menu\UpdateImagesControl;
use Caloriscz\Menus\MenuEditorControl;
use Caloriscz\Menus\MenuListControl;
use Nette,
    App\Model;

/**
 * Menu presenter.
 */
class MenuPresenter extends BasePresenter
{
    protected function createComponentMenuEditor()
    {
        return new MenuEditorControl($this->database, $this->em);
    }

    protected function createComponentMenuList()
    {
        return new MenuListControl($this->database);
    }

    protected function createComponentMenuInsert()
    {
        return new InsertMenuControl($this->database);
    }

    protected function createComponentMenuEdit()
    {
        return new EditMenuControl($this->database);
    }

    protected function createComponentMenuUpdateImages()
    {
        return new UpdateImagesControl($this->database);
    }

    protected function createComponentMenuMenusEditForm()
    {
        return new MenuMenusEditControl($this->database);
    }

    /**
     * Delete image
     * @param $identifier
     * @throws Nette\Application\AbortException
     */
    public function handleDeleteImage($identifier)
    {
        $type = $this->getParameter('type');

        Model\IO::remove(APP_DIR . '/images/menu/' . $identifier . $type . '.png');

        $this->redirect(':Admin:Menu:detail', ['id' => $identifier]);
    }

    public function renderDetail()
    {
        $this->template->menu = $this->database->table('menu')->get($this->getParameter('id'));
    }

    public function renderMenu()
    {
        $this->template->menu = $this->database->table('menu_menus')->get($this->getParameter('id'));
    }
}