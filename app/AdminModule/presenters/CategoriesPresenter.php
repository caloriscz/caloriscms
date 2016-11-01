<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Categories presenter.
 */
class CategoriesPresenter extends BasePresenter
{
    protected function createComponentCategoryEdit()
    {
        $control = new \Caloriscz\Categories\EditCategoryControl($this->database);
        return $control;
    }

   protected function createComponentCategoryInsert()
   {
       $control = new \Caloriscz\Categories\InsertCategoryControl($this->database);
       return $control;
   }

    /**
     * Delete categories
     */
    function handleDelete($id)
    {
        $category = new Model\Category($this->database);

        $this->database->table("categories")->where("id", $category->getSubIds($id))
            ->delete();

        $this->redirect(":Admin:Categories:default");
    }

    function handleUp($id, $sorted)
    {
        $sortDb = $this->database->table("categories")->where(array(
            "sorted > ?" => $sorted,
            "parent_id" => $this->getParameter("category"),
        ))->order("sorted")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("categories")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("categories")->where(array("id" => $sort->id))
                ->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Categories:default", array("id" => null));
    }

    function handleDown($id, $sorted, $category)
    {
        $sortDb = $this->database->table("categories")->where(array(
            "sorted < ?" => $sorted,
            "parent_id" => $category,
        ))->order("sorted DESC")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("categories")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("categories")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->presenter->redirect(this, array("id" => null));
    }

    function renderDefault()
    {
        if ($this->getParameter('id')) {
            $categoryId = $this->getParameter('id');
        } else {
            $categoryId = null;
        }

        $this->template->database = $this->database;
        $this->template->menu = $this->database->table("categories")->where('parent_id', $categoryId)
            ->order("sorted DESC");
    }

    function renderDetail()
    {
        $this->template->menu = $this->database->table("categories")->get($this->getParameter("id"));
    }

}
