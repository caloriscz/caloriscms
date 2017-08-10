<?php

namespace Caloriscz\Settings\Languages;

use Nette\Application\UI\Control;

class InsertLanguageControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /* Insert new language */
    function createComponentInsertForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("language", "Jazyk");
        $form->addText("code", "Kód");

        $form->addSubmit('send', 'dictionary.main.Save')
            ->setAttribute("class", "btn btn-success");

        $form->onSuccess[] = [$this, "insertFormSucceeded"];
        $form->onValidate[] = [$this, "permissionValidated"];
        return $form;
    }

    function permissionValidated()
    {
        if ($this->presenter->template->member->users_roles->settings_edit == 0) {
            $this->presenter->flashMessage("Nemáte oprávnění k této akci", "error");
            $this->presenter->redirect(this);
        }
    }

    function insertFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $langExists = $this->database->table("languages")->where("title = ? OR code = ?",
            $form->values->language, $form->values->code);

        if ($langExists->count() > 0) {
            $this->presenter->flashMessage("Název jazyka nebo kód již existuje", "error");
            $this->presenter->redirect(this);
        } else {
            $this->database->table("languages")->insert(array(
                "title" => $form->values->language,
                "code" => $form->values->code,
            ));

            $this->presenter->redirect(this);
        }
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/InsertLanguageControl.latte');

        $template->render();
    }

}