<?php
namespace Caloriscz\Categories;

use Nette\Application\UI\Control;

class InsertCategoryControl extends Control
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
    protected function createComponentInsertForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("parent");
        $form->addText('title', 'dictionary.main.Title')
            ->setAttribute("class", "form-control");
        $form->addSubmit('submitm', 'dictionary.main.Insert')
            ->setAttribute("class", "btn btn-primary");

        $form->onSuccess[] = [$this, "insertFormSucceeded"];
        $form->onValidate[] = [$this, "validateFormSucceeded"];
        return $form;
    }

    public function validateFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $category = $this->database->table("categories")->where(array(
            "parent_id" => $form->values->parent,
            "title" => $form->values->title,
        ));

        if ($category->count() > 0) {
            $this->presenter->flashMessage($this->presenter->translator->translate('messages.sign.categoryAlreadyExists'), "error");
            $this->presenter->redirect(this);
        }

        if ($form->values->title == "") {
            $this->presenter->flashMessage($this->translator->translate('messages.sign.categoryMustHaveSomeName'), "error");
            $this->presenter->redirect(this);
        }
    }

    public function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $category = new \App\Model\Category($this->database);
        $category->setCategory($form->values->title, $form->values->parent);

        $this->presenter->redirect(this, array("id" => null));
    }

    public function render($id)
    {
        $template = $this->template;
        $template->categoryId = $id;
        $template->setFile(__DIR__ . '/InsertCategoryControl.latte');

        $template->render();
    }

}