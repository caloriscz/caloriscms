<?php

namespace App\Forms\Contacts;

use App\Model\Helpdesk;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Security\Passwords;
use Nette\Utils\Random;

class SendLoginControl extends Control
{

    /** @var Context */
    public $database;

    public $onSave;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Send member login information
     */
    protected function createComponentSendLoginForm()
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addHidden('contact_id');
        $form->addCheckbox('sendmail', '\xC2\xA0' . 'Odeslat e-mail s přihlašovacími informacemi')
            ->setValue(0);

        $form->setDefaults(array(
            'contact_id' => $this->getPresenter()->getParameter('id'),
        ));

        $form->addSubmit('submitm', 'Zaslat uživateli')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = [$this, 'sendLoginFormSucceeded'];

        return $form;
    }

    public function sendLoginFormSucceeded(BootstrapUIForm $form)
    {
        $pwd = Random::generate(10);
        $pwdEncrypted = Passwords::hash($pwd);
        $user = $this->database->table('users')->get($form->values->contact_id);

        $this->database->table('users')->get($user->id)->update(array(
            'password' => $pwdEncrypted,
        ));

        if ($form->values->sendmail) {
            $params = array(
                'username' => $user->username,
                'email' => $user->email,
                'password' => $pwd,
            );

            $helpdesk = new Helpdesk($this->database, $this->getPresenter()->mailer);
            $helpdesk->setId(4);
            $helpdesk->setEmail($user->email);
            $helpdesk->setSettings($this->getPresenter()->template->settings);
            $helpdesk->setParams($params);
            $helpdesk->send();

            $pwd = null;
        }

        $this->onSave($form->values->contact_id, $pwd);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/SendLoginControl.latte');
        $this->template->render();
    }

}
