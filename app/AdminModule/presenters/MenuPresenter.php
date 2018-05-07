<?php

namespace App\AdminModule\Presenters;

use App\Forms\Menu\EditMenuControl;
use App\Forms\Menu\InsertMenuControl;
use App\Forms\Menu\MenuMenusEditControl;
use App\Forms\Menu\UpdateImagesControl;
use App\Model\IO;
use Caloriscz\Menus\MenuEditorControl;
use Caloriscz\Menus\MenuListControl;
use Nette\Application\AbortException;

/**
 * Menu presenter.
 */
class MenuPresenter extends BasePresenter
{
    protected function createComponentMenuEditor(): MenuEditorControl
    {
        return new MenuEditorControl($this->database, $this->em);
    }

    protected function createComponentMenuList(): MenuListControl
    {
        return new MenuListControl($this->database);
    }

    protected function createComponentMenuInsert(): InsertMenuControl
    {
        return new InsertMenuControl($this->database);
    }

    protected function createComponentMenuEdit(): EditMenuControl
    {
        return new EditMenuControl($this->database);
    }

    protected function createComponentMenuUpdateImages(): UpdateImagesControl
    {
        return new UpdateImagesControl($this->database);
    }

    protected function createComponentMenuMenusEditForm(): MenuMenusEditControl
    {
        return new MenuMenusEditControl($this->database);
    }

    /**
     * Delete image
     * @param $identifier
     * @throws AbortException
     */
    public function handleDeleteImage($identifier)
    {
        $type = $this->getParameter('type');

        IO::remove(APP_DIR . '/images/menu/' . $identifier . $type . '.png');

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