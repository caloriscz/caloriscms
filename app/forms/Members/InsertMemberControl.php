<?php

namespace App\Forms\Members;

use App\Model\Helpdesk;
use App\Model\MemberModel;
use Nette\Application\UI\Control;
use Nette\Database\Context;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;
use Nette\Security\Passwords;
use Nette\Utils\Random;
use Nette\Utils\Validators;

class InsertMemberControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public $onSave;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * Insert new user
     * @return BootstrapUIForm
     */
    protected function createComponentInsertForm(): BootstrapUIForm
    {
        $roles = $this->database->table('users_roles')->fetchPairs('id', 'title');
        $form = new BootstrapUIForm();

        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addText('username', 'Uživatel')
            ->setRequired(false)
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít aspoň %d znaků', 3);
        $form->addText('email', 'E-mail');

        if ($this->presenter->template->member->username === 'admin') {
            $form->addSelect('role', 'Uživatelská role', $roles)
                ->setAttribute('class', 'form-control');
        }
        $form->addCheckbox('sendmail', 'Odeslat přihlašovací e-mail')
            ->setValue(1);

        $form->addSubmit('submitm', 'Vytvořit')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'insertFormValidated'];

        return $form;
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function insertFormValidated(BootstrapUIForm $form): void
    {
        $member = new MemberModel($this->database);
        $userExists = $member->getUserName($form->values->username);
        $emailExists = $member->getEmail($form->values->email);

        if (!$this->getPresenter()->template->member->users_roles->members) {
            $this->onSave('Nemáte oprávnění');
        }

        if (Validators::isEmail($form->values->email) === false) {
            $this->onSave('Zadejte platnou e-mailovou adresu', false);
        } elseif ($emailExists > 0) {
            $this->onSave('E-mail již existuje', false);
        } elseif ($userExists > 0) {
            $this->onSave('Uživatel již existuje', false);
        }
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        $pwd = Random::generate(10);
        $pwdEncrypted = Passwords::hash($pwd);

        $userId = $this->database->table('users')
            ->insert([
                'email' => $form->values->email,
                'username' => $form->values->username,
                'password' => $pwdEncrypted,
                'date_created' => date('Y-m-d H:i:s'),
                'users_roles_id' => $form->values->role,
                'state' => 1,
            ]);

        if ($form->values->sendmail) {
            $params = [
                'username' => $form->values->username,
                'password' => $pwd
            ];

            $helpdesk = new Helpdesk($this->database, $this->presenter->mailer);
            $helpdesk->setId(5);
            $helpdesk->setEmail($form->values->email);
            $helpdesk->setSettings($this->presenter->template->settings);
            $helpdesk->setParams($params);
            $helpdesk->send();
        }

        $this->onSave(false, $userId);
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/InsertMemberControl.latte');
        $this->template->render();
    }

}
