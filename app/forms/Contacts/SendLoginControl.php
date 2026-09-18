<?php

namespace App\Forms\Contacts;

use App\Security\PasswordReset;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;

class SendLoginControl extends Control
{

    public $database;
    public $onSave;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Send member login information
     */
    protected function createComponentSendLoginForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->addProtection('Platnost formuláře vypršela. Zkuste to znovu.');
        $form->getElementPrototype()->class = 'form-horizontal';


        $form->addHidden('contact_id');

        $form->setDefaults([
            'contact_id' => $this->getPresenter()->getParameter('id'),
        ]);

        $form->addSubmit('submitm', 'Zaslat uživateli')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = [$this, 'sendLoginFormSucceeded'];

        return $form;
    }

    public function sendLoginFormSucceeded(BootstrapUIForm $form): void
    {
        $role = $this->presenter->template->memberRole;
        \App\Security\AdminPermissions::requirePermission($role ? $role->toArray() : [], 'members');
        $userId = (int) $this->presenter->getParameter('id');
        if ($userId !== (int) $form->values->contact_id) {
            $form->addError('Uživatel není platný.');
            return;
        }
        if (!(new PasswordReset($this->database))->send($userId, $this->presenter->mailer,
            $this->presenter->template->settings)) {
            $form->addError('Odkaz nelze odeslat. Ověřte aktivní účet a e-mail, případně zkuste znovu za minutu.');
            return;
        }
        $this->onSave($userId);
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/SendLoginControl.latte');
        $this->template->render();
    }

}
