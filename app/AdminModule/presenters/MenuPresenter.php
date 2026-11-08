<?php

namespace App\AdminModule\Presenters;

use App\Forms\Menu\EditMenuControl;
use App\Forms\Menu\InsertMenuControl;
use App\Forms\Menu\InsertMenuMenusControl;
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
        return new MenuEditorControl($this->database);
    }

    protected function createComponentMenuList(): MenuListControl
    {
        return new MenuListControl($this->database);
    }

    protected function createComponentMenuInsert(): InsertMenuControl
    {
        return new InsertMenuControl($this->database);
    }

    protected function createComponentMenuMenusInsert(): InsertMenuMenusControl
    {
        return new InsertMenuMenusControl($this->database);
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
    public function handleDeleteImage($identifier): void
    {
        $type = (string) $this->getParameter('type');
        if (!preg_match('/^[1-9][0-9]*$/D', (string) $identifier) || !in_array($type, ['', '_h', '_a', '_ah'], true)) {
            $this->error('Invalid menu image.', 400);
        }
        (new \App\Model\AssetStorage($this->database, APP_DIR))->run(static function ($assets) use ($identifier, $type): void {
            $assets->remove('images/menu/' . $identifier . $type . '.png');
        });

        $this->redirect(':Admin:Menu:detail', ['id' => $identifier]);
    }

    public function actionDetail(): void
    {
        $this->template->menu = $this->requireRecord('menu');
    }

    public function actionMenu(): void
    {
        $this->template->menu = $this->requireRecord('menu_menus');
    }
}
