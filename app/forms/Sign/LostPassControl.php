<?php

namespace App\Forms\Sign;

use App\Security\PasswordReset;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class LostPassControl extends Control
{

    public Explorer $database;

    public $onSave;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     * @return BootstrapUIForm
     */
    protected function createComponentSendForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->addProtection('Platnost formuláře vypršela. Zkuste to znovu.');

        $form->addHidden('layer');
        $form->addEmail('email', 'E-mail')->setRequired('Zadejte e-mail.');
        $form->addSubmit('submitm', 'Odeslat');

        $form->onSuccess[] = [$this, 'sendFormSucceeded'];
        return $form;
    }

    public function sendFormSucceeded(BootstrapUIForm $form): void
    {
        $users = $this->database->table('users')->where('email', $form->values->email);
        if ($users->count() === 1) {
            (new PasswordReset($this->database))->send((int) $users->fetch()->id,
                $this->presenter->mailer, $this->presenter->template->settings);
        }
        // Same response for unknown, disabled, duplicate, throttled and sent cases.
        $this->onSave(false);
    }

    public function render($layer = 'front'): void
    {
        $this->template->setFile(__DIR__ . '/LostPassControl.latte');
        $this->template->layer = $layer;
        $this->template->render();
    }

}
