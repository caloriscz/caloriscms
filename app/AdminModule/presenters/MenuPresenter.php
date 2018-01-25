<?php

namespace App\AdminModule\Presenters;

use Caloriscz\Menus\MenuForms\EditMenuControl;
use Caloriscz\Menus\MenuForms\InsertMenuControl;
use Caloriscz\Menus\MenuForms\UpdateImagesControl;
use Nette,
    App\Model;

/**
 * Menu presenter.
 */
class MenuPresenter extends BasePresenter
{

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

    /**
     * Delete categories
     * @param $id
     * @throws Nette\Application\AbortException
     */
    public function handleDelete($id)
    {
        $menu = new Model\Menu($this->database);

        $this->database->table('menu')->where('id', $menu->getSubIds($id))->delete();

        $this->redirect(this, ['id' => null]);
    }

    /**
     * Delete image
     * @param $id
     * @throws Nette\Application\AbortException
     */
    public function handleDeleteImage($id)
    {
        $type = $this->getParameter('type');

        Model\IO::remove(APP_DIR . '/images/menu/' . $id . $type . '.png');

        $this->redirect(':Admin:Menu:detail', ['id' => $id]);
    }

    /**
     * Move up menu item
     * @param $identifier
     * @param $sorted
     * @throws Nette\Application\AbortException
     */
    public function handleUp($identifier, $sorted)
    {
        $sortDb = $this->database->table('menu')->where([
            'sorted > ?' => $sorted,
            'parent_id' => $this->getParameter('category'),
        ])->order('sorted')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('menu')->where(['id' => $identifier])->update(['sorted' => $sort->sorted]);
            $this->database->table('menu')->where(['id' => $sort->id])
                ->update(array('sorted' => $sorted));
        }

        $this->redirect(':Admin:Menu:default', ['id' => null]);
    }

    /**
     * Move down menu item
     * @param $identifier
     * @param $sorted
     * @param $category
     * @throws Nette\Application\AbortException
     */
    public function handleDown($identifier, $sorted, $category)
    {
        $sortDb = $this->database->table('menu')->where([
            'sorted < ?' => $sorted,
            'parent_id' => $category,
        ])->order('sorted DESC')->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table('menu')->where(['id' => $identifier])->update(['sorted' => $sort->sorted]);
            $this->database->table('menu')->where(['id' => $sort->id])->update(['sorted' => $sorted]);
        }

        $this->redirect('this', ['id' => null]);
    }

    public function renderDefault()
    {
        $categoryId = null;

        if ($this->getParameter('id')) {
            $categoryId = $this->getParameter('id');
        }

        $this->template->database = $this->database;
        $this->template->menu = $this->database->table('menu')->where('parent_id', $categoryId)
            ->order('sorted DESC');
    }

    public function renderDetail()
    {
        $this->template->menu = $this->database->table('menu')->get($this->getParameter('id'));
    }

}