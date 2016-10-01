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

    /**
     * Insert category
     */
    protected function createComponentInsertCategoryForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("parent");
        $form->addText('title', 'dictionary.main.Title')
            ->setAttribute("class", "form-control");
        $form->addSubmit('submitm', 'dictionary.main.Insert')
            ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = $this->insertCategoryFormSucceeded;
        $form->onValidate[] = $this->validateCategoryFormSucceeded;
        return $form;
    }

    public function validateCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $category = $this->database->table("categories")->where(array(
            "parent_id" => $form->values->parent,
            "title" => $form->values->title,
        ));

        if ($category->count() > 0) {
            $this->flashMessage($this->translator->translate('messages.sign.categoryAlreadyExists'), "error");
            $this->redirect(":Admin:Categories:categories");
        }

        if ($form->values->title == "") {
            $this->flashMessage($this->translator->translate('messages.sign.categoryMustHaveSomeName'), "error");
            $this->redirect(":Admin:Categories:categories");
        }
    }

    public function insertCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $category = new Model\Category($this->database);
        $category->setCategory($form->values->title, $form->values->parent);

        $this->redirect(":Admin:Categories:default", array("id" => null));
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

        $this->redirect(":Admin:Categories:default", array("id" => null));
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
