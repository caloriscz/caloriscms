<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Link management with categories
 */
class LinksPresenter extends BasePresenter {

    protected function startup() {
        parent::startup();

        $this->template->link = $this->database->table("links")
                ->get($this->getParameter("id"));
    }

    protected function createComponentCategoryPanel()
    {
        $control = new \Caloriscz\Links\LinkForms\CategoryPanelControl($this->database);
        return $control;
    }

    /**
     * Insert contact
     */
    function createComponentInsertForm() {
        $form = $this->baseFormFactory->createPH();

        $form->addSubmit("submitm", "Vytvořit nový odkaz")
                ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapPHForm $form) {
        $id = $this->database->table("links")
                ->insert(array(
            "links_categories_id" => null,
        ));

        $this->redirect(":Admin:Links:detail", array("id" => $id));
    }

    /**
     * Delete contact
     */
    function handleDelete($id) {
        $this->database->table("links")->get($id)->delete();

        $this->redirect(":Admin:Links:default", array("id" => null));
    }

    /**
     * Edit contact
     */
    function createComponentEditForm() {
        $categories = $this->database->table("links_categories")
                        ->where("parent_id = ? OR id = ?", $this->template->settings['categories:id:link'], $this->template->settings['categories:id:link'])
                        ->order("title")->fetchPairs("id", "title");
        $form = $this->baseFormFactory->createUI();
        $form->addHidden('id');
        $form->addText("title", "dictionary.main.Title")
                ->setAttribute("placeholder", "dictionary.main.Title");
        $form->addText("url", "dictionary.main.URL")
                ->setAttribute("placeholder", "dictionary.main.URL");
        $form->addSelect("category", "dictionary.main.Category", $categories)
                ->setAttribute("class", "form-control");
        $form->addTextArea("description", "dictionary.main.Description")
                ->setAttribute("class", "form-control")
                ->setHtmlId("wysiwyg");
        $form->addUpload("the_file", "Vyberte obrázek (nepovinné)");
        $form->setDefaults(array(
            "title" => $this->template->link->title,
            "url" => $this->template->link->url,
            "category" => $this->template->link->links_categories_id,
            "description" => $this->template->link->description,
            "id" => $this->getParameter("id"),
        ));

        $form->addSubmit("submitm", "dictionary.main.Save")
                ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form) {
        $this->database->table("links")
                ->where(array(
                    "id" => $form->values->id,
                ))
                ->update(array(
                    "title" => $form->values->title,
                    "url" => $form->values->url,
                    "links_categories_id" => $form->values->category,
                    "description" => $form->values->description,
        ));

        $uid = \Nette\Utils\Strings::padLeft($form->values->id, 6, "0");

        if (file_exists(APP_DIR . '/links/' . $uid . ".jpg") && is_uploaded_file($_FILES['the_file']['tmp_name'])) {
            \App\Model\IO::remove(APP_DIR . '/links/' . $uid . ".jpg");
        }
        \App\Model\IO::upload(APP_DIR . '/links', $uid . ".jpg", 0644);

        $this->redirect(":Admin:Links:detail", array("id" => $form->values->id));
    }

    function handleDeleteImage($id) {
        \App\Model\IO::remove(APP_DIR . '/links/' . \Nette\Utils\Strings::padLeft($id, 6, '0') . '.jpg');
        $this->redirect(":Admin:Links:detail", array("id" => $id, "rnd" => \Nette\Utils\Random::generate(4)));
    }

    /**
     * Delete group
     */
    function handleDeleteCategory($id) {
        if ($id == 1) {
            $this->flashMessage($this->translator->translate('messages.sign.CantDeleteMainGroup'), "error");
            $this->redirect(":Admin:Links:categories");
        }

        $this->database->table("links")->where(array("links_categories_id" => $id))->update(array("links_categories_id" => 1));

        $this->database->table("links_categories")->get($id)->delete();

        $this->redirect(":Admin:Links:categories");
    }

    public function renderDefault() {
        if ($this->getParameter("id")) {
            $this->template->links = $this->database->table("links")
                    ->where("links_categories_id", $this->getParameter("id"))
                    ->order("title");
        } else {
            $this->template->links = $this->database->table("links")->order("title");
        }
    }

    public function renderCategories() {
        $this->template->categories = $this->database->table("links_categories")->order("category");
    }

}
