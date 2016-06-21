<?php

use Nette\Application\UI\Control;

class NewsletterFormControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Newsletter
     * @return \Nette\Forms\BootstrapUIForm
     */
    function createComponentAdd()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "";
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("email")
                ->setAttribute("class", "form-control");
        $form->addSubmit("submitm", "messages.helpdesk.send")
                ->setAttribute("class", "btn btn-yellow");

        $form->onValidate[] = $this->addValidated;
        $form->onSuccess[] = $this->addSucceeded;
        return $form;
    }

    function addValidated(\Nette\Forms\BootstrapPHForm $form)
    {
        $dbEmail = $this->database->table("newsletter")->where(array("email" => $form->values->email));

        if ($dbEmail->count() > 0) {
            $this->flashMessage("Váš e-mail byl již přidán");
            $this->presenter->redirect(":Front:Homepage:default");
        }
    }

    function addSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $this->database->table("contacts")->insert(array(
            "email" => $form->values->email,
            "categories_id" => $this->template->settings['categories:id:contactsNewsletter'],
        ));

        $this->presenter->redirect(":Front:Homepage:default");
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/NewsletterFormControl.latte');
        $template->settings = $this->presenter->template->settings;

        $template->render();
    }

}
