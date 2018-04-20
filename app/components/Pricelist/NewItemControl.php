<?php

namespace Caloriscz\Pricelist;

use Nette\Application\UI\Control;

class NewItemControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Menu Insert
     */
    protected function createComponentInsertForm()
    {
        $category = $this->database->table("pricelist_categories")->order("id")->fetchPairs('id', 'title');
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText('title', 'dictionary.main.Title')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadávejte delší text', 1);
        $form->addSelect('category', 'dictionary.main.Categories', $category)
            ->setAttribute("class", "form-control");
        $form->addText('price', 'Cena')
            ->addRule(\Nette\Forms\Form::INTEGER, 'Zadávejte pouze čísla')
            ->setAttribute("style", "width: 80px; text-align: right;");
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->onSuccess[] = $this->insertFormSucceeded;
        return $form;
    }

    public function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $max = $this->database->table("pricelist")->max("sorted") + 1;

        $id = $this->database->table("pricelist")->insert(array(
            "title" => $form->values->title,
            "pricelist_categories_id" => $form->values->category,
            "price" => $form->values->price,
            "sorted" => $max,
        ));

        $this->presenter->redirect(":Admin:Pricelist:menuedit", array("id" => $id));
    }


    public function render()
    {
        $template = $this->template;

        $getParams = $this->getParameters();
        unset($getParams["page"]);
        $template->args = $getParams;

        $template->setFile(__DIR__ . '/NewItemControl.latte');

        $template->idActive = $this->presenter->getParameter("id");
        $template->menu = $this->database->table('pricelist_categories');
        $template->render();
    }

}
