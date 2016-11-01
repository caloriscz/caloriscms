<?php

namespace Caloriscz\Profile;

use Nette\Application\UI\Control;

class EditControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Edit your profile
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addGroup("Osobní údaje");
        $form->addText("username", "Uživatel")
            ->setAttribute("style", "border: 0; font-size: 1.5em;")
            ->setDisabled();
        $form->addRadioList('sex', 'Pohlaví', array(
            1 => "\xC2\xA0" . 'žena',
            2 => "\xC2\xA0" . 'muž',
        ))->setAttribute("class", "checkboxlistChoose");

        $form->setDefaults(array(
            "username" => $this->presenter->template->member->username,
            "sex" => $this->presenter->template->member->sex,
        ));

        $form->addSubmit("submit", "dictionary.main.Save");
        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("users")->where(array(
            "id" => $this->presenter->user->getId()
        ))
            ->update(array(
                "sex" => $form->values->sex,
            ));

        $this->presenter->redirect(this);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditControl.latte');


        $template->render();
    }

}
