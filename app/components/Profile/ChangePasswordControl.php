<?php

namespace Caloriscz\Profile;

use Nette\Application\UI\Control;

class ChangePasswordControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     * @return string
     */
    function createComponentChangePasswordForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addPassword("password1", "dictionary.main.Password");
        $form->addPassword("password2", "Znovu napište heslo");
        $form->addSubmit('name', 'dictionary.main.Change');

        $form->onSuccess[] = $this->changePasswordFormSucceeded;
        return $form;
    }

    function changePasswordFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $ppwd = $form->values->password1;
        $ppwd2 = $form->values->password2;

        $passwordEncrypted = \Nette\Security\Passwords::hash($ppwd);

        if (strcasecmp($ppwd, $ppwd2) != 0) {
            $this->presenter->flashMessage('Hesla se neshodují');
        }

        $this->database->table("users")->where(array(
            "id" => $this->presenter->user->getId(),
        ))->update(array(
            "password" => $passwordEncrypted,
        ));

        setcookie("calpwd", $passwordEncrypted, time() + time() + 60 * 60 * 24 * 30, "/");

        $this->presenter->redirect(this);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ChangePasswordControl.latte');


        $template->render();
    }

}
