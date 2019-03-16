<?php

namespace App\Forms\Sign;

use App\Model\Helpdesk;
use App\Model\MemberModel;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Utils\Random;
use Nette\Utils\Validators;

class LostPassControl extends Control
{

    /** @var Context */
    public $database;

    public $onSave;

    public function __construct(Context $database)
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

        $form->addHidden('layer');
        $form->addText('email', 'E-mail');
        $form->addSubmit('submitm', 'Odeslat');

        $form->onSuccess[] = [$this, 'sendFormSucceeded'];
        $form->onValidate[] = [$this, 'sendFormValidated'];
        return $form;
    }

    public function sendFormValidated(BootstrapUIForm $form): void
    {
        if (!Validators::isEmail($form->values->email)) {
            $this->onSave('Adresa je neplatná');
        }

        if ($this->database->table('users')->where(['email' => $form->values->email])->count() === 0) {
            $this->onSave('E-mail nenalezen');
        }
    }

    public function sendFormSucceeded(BootstrapUIForm $form): void
    {
        $passwordGenerate = Random::generate(12, '987654321zyxwvutsrqponmlkjihgfedcba');

        $member = new MemberModel($this->database);
        $member->setActivation($form->values->email, $passwordGenerate);

        $params = [
            'code' => $passwordGenerate,
            'email' => $form->values->email,
        ];

        $helpdesk = new Helpdesk($this->database, $this->presenter->mailer);
        $helpdesk->setId(11);
        $helpdesk->setEmail($form->values->email);
        $helpdesk->setSettings($this->presenter->template->settings);
        $helpdesk->setParams($params);
        $helpdesk->send();

        $this->onSave(false);
    }

    public function render($layer = 'front'): void
    {
        $this->template->setFile(__DIR__ . '/LostPassControl.latte');
        $this->template->layer = $layer;
        $this->template->render();
    }

}
