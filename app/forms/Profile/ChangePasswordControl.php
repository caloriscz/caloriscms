<?php

namespace App\Forms\Profile;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;
use Nette\Security\Passwords;

class ChangePasswordControl extends Control
{
    private const MIN_PASSWORD_LENGTH = 8;

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
        $form->addPassword('password1', 'Heslo')
            ->setRequired('Zadejte heslo.')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', self::MIN_PASSWORD_LENGTH);
        $form->addPassword('password2', 'Znovu napište heslo')
            ->setRequired('Zadejte heslo znovu.')
            ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['password1']);
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

        if (strcasecmp($ppwd, $ppwd2) !== 0) {
            $this->presenter->flashMessage('Hesla se neshodují', 'error');
            $this->presenter->redirect('this');
        }

        $passwordHash = new Passwords();
        $passwordEncrypted = $passwordHash->hash($ppwd);

        $this->database->table('users')->where(['id' => $this->presenter->user->getId()])->update(
            ['password' => $passwordEncrypted]
        );

        setcookie('calpwd', '', time() - 3600, '/');

        $this->presenter->redirect('this');
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ChangePasswordControl.latte');
        $template->render();
    }
}
