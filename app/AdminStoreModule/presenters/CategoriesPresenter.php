<?php

namespace App\AdminStoreModule\Presenters;

use Nette,
    App\Model;

/**
 * Categories presenter.
 */
class CategoriesPresenter extends BasePresenter
{

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
        $category = $this->database->table("pages")->where(array(
            "pages_id" => $form->values->parent,
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
        $doc = new Model\Document($this->database);
        $doc->setType(7);
        $doc->setParent($form->values->parent);
        $doc->setTitle($form->values->title);
        $page = $doc->create($this->user->getId());
        Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page, 0755);

        $this->redirect(":AdminStore:Categories:default", array("id" => null));
    }

    /**
     * Edit category
     */
    protected function createComponentUpdateCategoryForm()
    {
        $category = $this->database->table("pages")->get($this->getParameter("id"));
        $categories = $this->getAll();
        unset($categories[$category->id]);

        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addSelect('parent', 'Nadřazená kategorie', $categories)
            ->setPrompt('- hlavní kategorie -')
            ->setAttribute("class", "form-control");
        $form->addSubmit('submitm', 'dictionary.main.Save');


        $arr = array(
            "id" => $category->id,
            "parent" => $category->pages_id,
        );

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = $this->updateCategoryFormSucceeded;
        return $form;
    }

    public function updateCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("categories")->get($form->values->id)
            ->update(array(
                "pages_id" => $form->values->parent,
            ));

        $this->redirect(":AdminStore:Categories:detail", array("id" => $form->values->id));
    }

    /**
     * Delete categories
     */
    function handleDelete($id)
    {
        $category = new Model\Category($this->database);

        $this->database->table("pages")->where("id", $category->getSubIds($id))
            ->delete();

        $this->redirect(":AdminStore:Categories:default");
    }

    function handleUp($id, $sorted)
    {
        $sortDb = $this->database->table("pages")->where(array(
            "sorted > ?" => $sorted,
            "pages_id" => $this->getParameter("category"),
        ))->order("sorted")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("categories")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("categories")->where(array("id" => $sort->id))
                ->update(array("sorted" => $sorted));
        }

        $this->redirect(":AdminStore:Categories:default", array("id" => null));
    }

    function handleDown($id, $sorted, $category)
    {
        $sortDb = $this->database->table("pages")->where(array(
            "sorted < ?" => $sorted,
            "pages_id" => $category,
        ))->order("sorted DESC")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("pages")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("pages")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(":AdminStore:Categories:default", array("id" => null));
    }

    /**
     * Get published Categories as an array
     * @return aray of categories with id
     */
    function getAll()
    {
        foreach ($categoryDb = $this->database->table("pages")->where('pages_types_id = 7')->order("title") as $categories) {
            $category[$categories->id] = $categories->title;
        }

        return $category;
    }

    function renderDefault()
    {
        if ($this->getParameter('id')) {
            $categoryId = $this->getParameter('id');
        } else {
            $categoryId = null;
        }

        $this->template->database = $this->database;
        $this->template->menu = $this->database->table("pages")->where(array('pages_id' => $categoryId, 'pages_types_id' => 7))
            ->order("sorted DESC");
    }

    function renderDetail()
    {
        $this->template->page = $this->database->table("pages")->get($this->getParameter("id"));
    }

}
