<?php

namespace Caloriscz\Contacts\ContactForms;

use Nette\Application\UI\Control;

class SendLoginControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public $onSave;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Send member login information
     */
    function createComponentSendLoginForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden("contact_id");
        $form->addCheckbox("sendmail", "\xC2\xA0" . "Odeslat e-mail s přihlašovacími informacemi")
            ->setValue(0);

        $form->setDefaults(array(
            "contact_id" => $this->presenter->getParameter('id'),
        ));

        $form->addSubmit('submitm', 'Zaslat uživateli')->setAttribute("class", "btn btn-success");
        $form->onSuccess[] = [$this, "sendLoginFormSucceeded"];

        return $form;
    }

    function sendLoginFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $pwd = \Nette\Utils\Random::generate(10);
        $pwdEncrypted = \Nette\Security\Passwords::hash($pwd);
        $user = $this->database->table('users')->get($form->values->contact_id);

        $this->database->table("users")->get($user->id)->update(array(
            "password" => $pwdEncrypted,
        ));

        if ($form->values->sendmail) {
            $params = array(
                'username' => $user->username,
                'email' => $user->email,
                'password' => $pwd,
            );

            $helpdesk = new \App\Model\Helpdesk($this->database, $this->presenter->mailer);
            $helpdesk->setId(4);
            $helpdesk->setEmail($user->email);
            $helpdesk->setSettings($this->presenter->template->settings);
            $helpdesk->setParams($params);
            $helpdesk->send();

            $pwd = null;
        }

        $this->onSave($form->values->contact_id, $pwd);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/SendLoginControl.latte');

        $template->render();
    }

}
