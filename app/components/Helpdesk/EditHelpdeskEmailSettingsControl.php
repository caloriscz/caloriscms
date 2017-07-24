<?php

namespace Caloriscz\Helpdesk;

use Nette\Application\UI\Control;

class EditHelpdeskEmailSettingsControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public $onSave;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Send teste-mail
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';

        $helpdeskEmailsDb = $this->database->table("helpdesk_emails")->get($this->presenter->getParameter("id"));
        $templates = $this->database->table("helpdesk_templates");

        $form->addHidden("helpdesk_email_id");
        $form->addText("email", "dictionary.main.Email");
        $form->addSelect("helpdesk_templates_id", "Šablona", $templates->fetchPairs("id", "title"))
        ->setAttribute("class", "form-control");
        $form->addCheckbox("log", " Ukládat e-maily do databáze");
        $form->addSubmit('submitm', 'dictionary.main.Save');

        $form->setDefaults(array(
            "helpdesk_email_id" => $this->presenter->getParameter("id"),
            "email" => $helpdeskEmailsDb->email,
            "helpdesk_templates_id" => $helpdeskEmailsDb->helpdesk_templates_id,
            "log" => $helpdeskEmailsDb->log,
        ));

        $form->onSuccess[] = [$this, "editFormSucceeded"];
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("helpdesk_emails")->get($form->values->helpdesk_email_id)->update(array(
            "helpdesk_templates_id" => $form->values->helpdesk_templates_id,
            "email" => $form->values->email,
            "log" => $form->values->log,
        ));

        $this->onSave($form->values->helpdesk_email_id);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/EditHelpdeskEmailSettingsControl.latte');

        $template->render();
    }

}