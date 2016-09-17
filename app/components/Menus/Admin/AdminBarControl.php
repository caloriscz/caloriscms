<?php

namespace Caloriscz\Menus\Admin;

use Nette\Application\UI\Control;

class AdminBarControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert category
     */
    protected function createComponentInsertCategoryForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("parent_id");
        $form->addHidden("type");
        $form->addText('title', 'dictionary.main.title')
                ->setAttribute("class", "form-control");
        $form->addSubmit('submitm', 'dictionary.main.insert')
                ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = $this->insertCategoryFormSucceeded;
        $form->onValidate[] = $this->validateCategoryFormSucceeded;
        return $form;
    }

    public function validateCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $redirectTo = $this->presenter->getName();

        $category = $this->database->table("categories")->where(array(
            "parent_id" => $form->values->parent_id,
            "title" => $form->values->title,
            "type" => $form->values->type,
        ));

        if ($category->count() > 0) {
            $this->flashMessage($this->translator->translate('messages.sign.categoryAlreadyExists'), "error");
            $this->redirect(":" . $redirectTo . ":default", array("id" => null));
        }

        if ($form->values->title == "") {
            $this->flashMessage($this->translator->translate('messages.sign.categoryMustHaveSomeName'), "error");
            $this->redirect(":" . $redirectTo . ":default", array("id" => null));
        }
    }

    public function insertCategoryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $category = new Category($this->database);
        $category->setCategory($form->values->title, $form->values->parent_id);

        $redirectTo = $this->presenter->getName();

        $this->presenter->redirect(":" . $redirectTo . ":default", array("id" => $form->values->parent_id));
    }

    /**
     * 
     * @param type $id
     * @param type $type Specifiees type of category: store, media, blog, menu etc. Some categories have special needs
     */
    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        $role = $this->presenter->user->getRoles();
        $template->roleCheck = $this->database->table("users_roles")->get($role[0]);

        if ($template->roleCheck && $template->settings['site:admin:adminBarEnabled']) {
            $template->enabled = true;
        } else {
            $template->enabled = false;
        }

        $page = $this->database->table("pages")->get($this->presenter->getParameter("page_id"));

        if ($page) {
            $template->page = $page;
        } else {
            $template->page = false;
        }

        $template->presenterName = $this->presenter->getName();
        $template->presenterView = $this->presenter->getView();
        $template->slug = $this->presenter->getParameter("slug");

        $template->setFile(__DIR__ . '/AdminBarControl.latte');

        $template->render();
    }

}
