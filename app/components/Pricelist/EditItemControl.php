<?php

namespace Caloriscz\Pricelist;

use Nette\Application\UI\Control;

class EditItemControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Menu Edit
     */
    protected function createComponentEditForm()
    {
        $item = $this->database->table("pricelist")->get($this->presenter->getParameter("id"));
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('id');
        $form->addText('title', 'Výkon')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadávajte delší text', 1);
        $form->addTextArea('description', 'Popis')
            ->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Zadávajte delší text', 1)
            ->setHtmlId("wysiwyg-sm");
        $form->addText('price', 'Cena')
            ->addRule(\Nette\Forms\Form::INTEGER, 'Zadávajte pouze čísla')
            ->setAttribute("style", "width: 80px; text-align: right;");
        $form->addText('price_info'
            . '', 'Info za cenou');


        $form->setDefaults(array(
            "title" => $item->title,
            "description" => $item->description,
            "price" => $item->price,
            "price_info" => $item->price_info,
            "id" => $item->id,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    public function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("pricelist")->where(array(
            "id" => $form->values->id,
        ))->update(array(
            "title" => $form->values->title,
            "description" => $form->values->description,
            "price" => $form->values->price,
            "price_info" => $form->values->price_info,
        ));

        $this->presenter->redirect(":Admin:Pricelist:menuedit", array("id" => $form->values->id));
    }


    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditItemControl.latte');

        $template->render();
    }

}
