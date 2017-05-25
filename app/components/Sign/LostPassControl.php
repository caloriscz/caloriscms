<?php

namespace Caloriscz\Sign;

use Nette\Application\UI\Control;
use Nette\Forms\BootstrapUIForm;

class LostPassControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public $onSave;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     * @return string
     */
    function createComponentSendForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("layer");
        $form->addText("email", "dictionary.main.Email");
        $form->addSubmit('submitm', 'dictionary.main.Send');

        $form->onSuccess[] = $this->sendFormSucceeded;
        $form->onValidate[] = $this->sendFormValidated;
        return $form;
    }

    function sendFormValidated(BootstrapUIForm $form)
    {
        if (!\Nette\Utils\Validators::isEmail($form->values->email)) {
            $this->onSave("Adresa je neplatná");
        }

        if ($this->database->table('users')->where(array('email' => $form->values->email))->count() == 0) {
            $this->onSave("E-mail nenalezen");
        }
    }

    function sendFormSucceeded(BootstrapUIForm $form)
    {
        $passwordGenerate = \Nette\Utils\Random::generate(12, "987654321zyxwvutsrqponmlkjihgfedcba");

        $member = new \App\Model\MemberModel($this->database);
        $member->setActivation($form->values->email, $passwordGenerate);

        $params = array(
            "code" => $passwordGenerate,
            "email" => $form->values->email,
        );

        $helpdesk = new \App\Model\Helpdesk($this->database, $this->presenter->mailer);
        $helpdesk->setId(11);
        $helpdesk->setEmail($form->values->email);
        $helpdesk->setSettings($this->presenter->template->settings);
        $helpdesk->setParams($params);
        $helpdesk->send();

        $this->onSave(false);
    }

    public function render($layer = 'front')
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/LostPassControl.latte');
        $template->addon = $this->database->table("addons");
        $template->layer = $layer;
        $template->member = $this->presenter->template->member;

        $template->render();
    }

}
