<?php

namespace App\Forms\Profile;

use DateTime;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Security\Passwords;

class ChangePasswordControl extends Control
{

    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    protected function createComponentChangePasswordForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addPassword('password1', 'dictionary.main.Password');
        $form->addPassword('password2', 'Znovu napište heslo');
        $form->addSubmit('name', 'dictionary.main.Change');

        $form->onSuccess[] = [$this, 'changePasswordFormSucceeded'];
        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     * @throws \Nette\Application\AbortException
     */
    protected function changePasswordFormSucceeded(BootstrapUIForm $form): void
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

        $date = new Datetime('+1 month');
        setcookie('calpwd', $passwordEncrypted, $date->format('C'), '/');

        $this->presenter->redirect('this');
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/ChangePasswordControl.latte');
        $template->render();
    }

    /**
     * Forces control to repaint.
     * @return void
     */
    function redrawControl()
    {
        // TODO: Implement redrawControl() method.
    }
}
