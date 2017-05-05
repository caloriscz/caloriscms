<?php

namespace Caloriscz\Sign;

use Nette\Application\UI\Control;

class ResetPassControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: Resets passwords in database, user fill in new password
     */
    public function createComponentResetForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden("email");
        $form->addHidden("code");
        $form->addPassword("password", "Nové heslo");
        $form->addPassword("password2", "Zopakujte nové heslo");
        $form->addSubmit('name', 'dictionary.main.Change');
        $form->setDefaults(array(
            "email" => $this->presenter->getParameter("email"),
            "code" => $this->presenter->getParameter("code"),
        ));

        $form->onSuccess[] = $this->resetFormSucceeded;
        return $form;
    }

    function resetFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $email = $form->values->email;
        $emailExistsDb = $this->database->table('users')->where(array(
            'email' => $email,
            'activation' => $form->values->code,
        ));

        if ($emailExistsDb->count() == 0) {
            $msg = 'Aktivace není platná';
        } elseif (strcmp($form->getValues()->password, $form->getValues()->password2) <> 0) {
            $msg = 'Hesla se neshodují';
        } else {
            $msg = 'Vytvořili jste nové heslo. Můžete se přihlásit.';
            $this->database->table("users")->where(array(
                "email" => $email,
            ))->update(array(
                "activation" => NULL,
                "password" => \Nette\Security\Passwords::hash($form->getValues()->password),
            ));
        }

        $this->presenter->flashMessage($msg, 'success');
        $this->presenter->redirect("Sign:in");
    }

    public function render($layer = 'front')
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ResetPassControl.latte');
        $template->addon = $this->database->table("addons");
        $template->layer = $layer;
        $template->member = $this->presenter->template->member;

        $template->render();
    }

}
