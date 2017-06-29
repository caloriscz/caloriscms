<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Menu presenter.
 */
class MenuPresenter extends BasePresenter
{

    protected function createComponentMenuInsert()
    {
        $control = new \Caloriscz\Menus\MenuForms\InsertMenuControl($this->database);
        return $control;
    }

    protected function createComponentMenuEdit()
    {
        $control = new \Caloriscz\Menus\MenuForms\EditMenuControl($this->database);
        return $control;
    }

    protected function createComponentMenuUpdateImages()
    {
        $control = new \Caloriscz\Menus\MenuForms\UpdateImagesControl($this->database);
        return $control;
    }

    /**
     * Delete categories
     */
    function handleDelete($id)
    {
        $menu = new Model\Menu($this->database);

        $this->database->table("menu")->where("id", $menu->getSubIds($id))->delete();

        $this->redirect(this, array("id" => null));
    }

    /**
     * Delete image
     */
    function handleDeleteImage($id)
    {
        $type = $this->getParameter("type");

        \App\Model\IO::remove(APP_DIR . '/images/menu/' . $id . $type . '.png');

        $this->redirect(":Admin:Menu:detail", array("id" => $id));
    }

    function handleUp($id, $sorted)
    {
        $sortDb = $this->database->table("menu")->where(array(
            "sorted > ?" => $sorted,
            "parent_id" => $this->getParameter("category"),
        ))->order("sorted")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("menu")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("menu")->where(array("id" => $sort->id))
                ->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Menu:default", array("id" => null));
    }

    function handleDown($id, $sorted, $category)
    {
        $sortDb = $this->database->table("menu")->where(array(
            "sorted < ?" => $sorted,
            "parent_id" => $category,
        ))->order("sorted DESC")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("menu")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("menu")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(this, array("id" => null));
    }

    function renderDefault()
    {
        if ($this->getParameter('id')) {
            $categoryId = $this->getParameter('id');
        } else {
            $categoryId = null;
        }

        $this->template->database = $this->database;
        $this->template->menu = $this->database->table("menu")->where('parent_id', $categoryId)
            ->order("sorted DESC");
    }

    function renderDetail()
    {
        $this->template->menu = $this->database->table("menu")->get($this->getParameter("id"));
    }

}