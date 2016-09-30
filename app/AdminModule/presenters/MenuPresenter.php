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

    /**
     * Edit category
     */
    protected function createComponentUpdateImagesForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('menu_id');
        $form->addUpload('the_file', 'dictionary.main.Image');
        $form->addUpload('the_file_2', 'Obrázek (hover)');
        $form->addUpload('the_file_3', 'Aktivní obrázek');
        $form->addUpload('the_file_4', 'Aktivní obrázek (hover)');

        $form->setDefaults(array("menu_id" => $this->getParameter("id")));

        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = $this->updateImagesFormSucceeded;
        return $form;
    }

    public function updateImagesFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        /* Main image */
        if ($form->values->the_file->error == 0) {
            copy($_FILES["the_file"]["tmp_name"], APP_DIR . "/images/menu/" . $form->values->menu_id . ".png");
            chmod(APP_DIR . "/images/menu/" . $form->values->menu_id . ".png", 0644);
        }

        /* Hover image */
        if ($form->values->the_file_2->error == 0) {
            copy($_FILES["the_file_2"]["tmp_name"], APP_DIR . "/images/menu/" . $form->values->menu_id . "_h.png");
            chmod(APP_DIR . "/images/menu/" . $form->values->menu_id . "_h.png", 0644);
        }

        /* Active image */
        if ($form->values->the_file_3->error == 0) {
            copy($_FILES["the_file_3"]["tmp_name"], APP_DIR . "/images/menu/" . $form->values->menu_id . "_a.png");
            chmod(APP_DIR . "/images/menu/" . $form->values->menu_id . "_a.png", 0644);
        }

        /* Active hover image */
        if ($form->values->the_file_4->error == 0) {
            copy($_FILES["the_file_4"]["tmp_name"], APP_DIR . "/images/menu/" . $form->values->menu_id . "_ah.png");
            chmod(APP_DIR . "/images/menu/" . $form->values->menu_id . "_ah.png", 0644);
        }


        $this->redirect(":Admin:Menu:detail", array("id" => $form->values->menu_id));
    }

    /**
     * Delete categories
     */
    function handleDelete($id)
    {
        $menu = new Model\Menu($this->database);

        $this->database->table("menu")->where("id", $menu->getSubIds($id))->delete();

        $this->redirect(":Admin:Menu:default", array("id" => null));
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