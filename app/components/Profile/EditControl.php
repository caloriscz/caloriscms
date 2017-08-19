<?php

namespace Caloriscz\Profile;

use Nette\Application\UI\Control;

class EditControl extends Control
{

    /** @var \Nette\Database\Context */
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

        $form->addText("username");
        $form->addRadioList('sex', '', array(
            1 => "\xC2\xA0" . 'žena', 2 => "\xC2\xA0" . 'muž',
        ));

        $form->setDefaults(array(
            "username" => $this->presenter->template->member->username,
            "sex" => $this->presenter->template->member->sex,
        ));

        $form->addSubmit("submit");
        $form->onSuccess[] = [$this, "editFormSucceeded"];
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
