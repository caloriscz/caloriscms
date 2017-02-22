<?php

namespace Caloriscz\Menus\Admin;

use Nette\Application\UI\Control;

class ContactCategoriesControl extends Control
{

    /** @var \Nette\Database\Context */
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

        $category = $this->database->table("contacts_categories")->where(array(
            "parent_id" => $form->values->parent_id,
            "title" => $form->values->title,
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
        if (is_numeric($form->values->type)) {
            $slugName = new \App\Model\Slug($this->database);
            $slugId = $slugName->insert($form->values->title, $form->values->type);
        }

        $category = new \App\Model\Category($this->database);
        $category->setCategory($form->values->title, $form->values->parent_id, $slugId);

        $redirectTo = $this->presenter->getName();

        $this->presenter->redirect(":" . $redirectTo . ":default", array("id" => $form->values->parent_id));
    }

    /**
     * 
     * @param type $id
     * @param type $type Specifies type of category: store, media, blog, menu etc. Some categories have special needs
     */
    public function render($id, $type = null)
    {
        $template = $this->template;
        $template->type = $type;

        $getParams = $this->getParameters();
        unset($getParams["page"]);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/ContactCategoriesControl.latte');

        $template->id = $id;
        $template->idActive = $this->presenter->getParameter("id");
        $template->menu = $this->database->table('contacts_categories')->where('parent_id', $id);
        $template->render();
    }

}