<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Categories presenter.
 */
class MenuPresenter extends BasePresenter
{

    /**
     * Insert menu
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
        $category = $this->database->table("menu")->where(array(
            "parent_id" => $form->values->parent,
            "title" => $form->values->title,
        ));

        if ($category->count() > 0) {
            $this->flashMessage($this->translator->translate('messages.sign.categoryAlreadyExists'), "error");
            $this->redirect(":Admin:Menu:categories");
        }

        if ($form->values->title == "") {
            $this->flashMessage($this->translator->translate('messages.sign.categoryMustHaveSomeName'), "error");
            $this->redirect(":Admin:Menu:categories");
        }
    }

    public function insertCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $category = new Model\Category($this->database);
        $category->setCategory($form->values->title, $form->values->parent);

        $this->redirect(":Admin:Menu:default", array("id" => null));
    }

    /**
     * Edit category
     */
    protected function createComponentUpdateCategoryForm()
    {
        $pages = new Model\Page($this->database);
        $categoryAll = new Model\Menu($this->database);
        $category = $this->database->table("menu")->get($this->getParameter("id"));
        $categories = $categoryAll->getAll();
        unset($categories[$category->id]);

        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addSelect('parent', 'Nadřazená kategorie', $categories)
            ->setPrompt('admin.categories.NothingRelated')
            ->setAttribute("class", "form-control");
        $form->addSelect('page', 'admin.categories.SelectPage', $pages->getPageList())
            ->setPrompt('admin.categories.PageSelectedManually')
            ->setAttribute("class", "form-control");
        $form->addText('url', 'dictionary.main.URL');
        $form->addUpload('the_file', 'dictionary.main.Icon');
        $form->addSubmit('submitm', 'dictionary.main.Save');


        $arr = array(
            "id" => $category->id,
            "title" => $category->title,
            "page" => $category->pages_id,
            "parent" => $category->parent_id,
            "url" => $category->url,
        );

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = $this->updateCategoryFormSucceeded;
        return $form;
    }

    public function updateCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($form->values->the_file->error == 0) {
            if (file_exists(APP_DIR . "/images/menu/" . $form->values->id . ".png")) {
                \App\Model\IO::remove(APP_DIR . "/images/menu/" . $form->values->id . ".png");
                \App\Model\IO::upload(APP_DIR . "/images/menu/", $form->values->id . ".png", 0644);
            } else {
                \App\Model\IO::upload(APP_DIR . "/images/menu/", $form->values->id . ".png", 0644);
            }
        }

        $slugName = new Model\Slug($this->database);

        if ($form->values->slug !== $form->values->slug_old && is_numeric($form->values->slug_type)) {
            $slugName->update($form->values->slug, $form->values->slug_old);
        }

        $this->database->table("categories")->get($form->values->id)
            ->update(array(
                "title" => $form->values->title,
                "pages_id" => $form->values->page,
                "parent_id" => $form->values->parent,
                "url" => $form->values->url,
            ));

        $this->redirect(":Admin:Menu:detail", array("id" => $form->values->id));
    }

    /**
     * Delete categories
     */
    function handleDelete($id)
    {
        $menu = new Model\Menu($this->database);

        $this->database->table("menu")->where("id", $menu->getSubIds($id))->delete();

        $this->redirect(":Admin:Menu:default");
    }

    /**
     * Delete image
     */
    function handleDeleteImage($id)
    {
        \App\Model\IO::remove(APP_DIR . '/images/categories/icons/' . $id . '.png');

        $this->redirect(":Admin:Menu:detail", array("id" => $id));
    }

    function handleUp($id, $sorted)
    {
        $sortDb = $this->database->table("categories")->where(array(
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
        $sortDb = $this->database->table("categories")->where(array(
            "sorted < ?" => $sorted,
            "parent_id" => $category,
        ))->order("sorted DESC")->limit(1);
        $sort = $sortDb->fetch();

        if ($sortDb->count() > 0) {
            $this->database->table("menu")->where(array("id" => $id))->update(array("sorted" => $sort->sorted));
            $this->database->table("menu")->where(array("id" => $sort->id))->update(array("sorted" => $sorted));
        }

        $this->redirect(":Admin:Menu:default", array("id" => null));
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
