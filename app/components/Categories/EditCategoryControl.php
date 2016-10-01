<?php
namespace Caloriscz\Categories;

use Nette\Application\UI\Control;

class EditCategoryControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentEditForm()
    {
        $categoryAll = new \App\Model\Category($this->database);
        $category = $this->database->table("categories")->get($this->presenter->getParameter("id"));
        $categories = $categoryAll->getAll();
        unset($categories[$category->id]);

        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('id');
        $form->addText('title', 'dictionary.main.Title');
        $form->addSelect('parent', 'Nadřazená kategorie', $categories)
            ->setPrompt('admin.categories.NothingRelated')
            ->setAttribute("class", "form-control");
        $form->addText('url', 'dictionary.main.URL');
        $form->addSubmit('submitm', 'dictionary.main.Save');


        $arr = array(
            "id" => $category->id,
            "title" => $category->title,
            "parent" => $category->parent_id,
        );

        $form->setDefaults(array_filter($arr));

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    public function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("categories")->get($form->values->id)
            ->update(array(
                "title" => $form->values->title,
                "parent_id" => $form->values->parent,
            ));

        $this->presenter->redirect(this, array("id" => $form->values->id));
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditCategoryControl.latte');

        $template->render();
    }

}