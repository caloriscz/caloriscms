<?php

namespace App\Forms\Profile;

use DateTime;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Security\Passwords;

class ChangePasswordControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    protected function createComponentChangePasswordForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->addPassword('password1', 'Heslo');
        $form->addPassword('password2', 'Znovu napište heslo');
        $form->addSubmit('name', 'Změnit');

        $form->onSuccess[] = [$this, 'changePasswordFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws AbortException
     */
    public function changePasswordFormSucceeded(BootstrapUIForm $form): void
    {
        $ppwd = $form->values->password1;
        $ppwd2 = $form->values->password2;
        $passwordEncrypted = Passwords::hash($ppwd);

        if (strcasecmp($ppwd, $ppwd2) !== 0) {
            $this->presenter->flashMessage('Hesla se neshodují');
        }

        $this->database->table('users')->where(['id' => $this->presenter->user->getId()])->update(
            ['password' => $passwordEncrypted]
        );

        setcookie('calpwd', $passwordEncrypted, time() + 15552000, '/');

        $this->presenter->redirect('this');
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ChangePasswordControl.latte');
        $template->render();
    }
}
