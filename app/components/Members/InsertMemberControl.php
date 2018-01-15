<?php

namespace Caloriscz\Members;

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
     */
    public function createComponentInsertForm()
    {
        $roles = $this->database->table('users_roles')->fetchPairs('id', 'title');
        $form = new BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText('username', 'dictionary.main.Member')
            ->setRequired(false)
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít aspoň %d znaků', 3);
        $form->addText('email', 'dictionary.main.Email');

        if ($this->presenter->template->member->username == 'admin') {
            $form->addSelect('role', 'dictionary.main.Role', $roles)
                ->setAttribute('class', 'form-control');
        }
        $form->addCheckbox('sendmail', 'messages.members.sendLoginEmail')
            ->setValue(1);

        $form->addSubmit('submitm', 'dictionary.main.Create')->setAttribute('class', 'btn btn-success');
        $form->onSuccess[] = [$this, 'insertFormSucceeded'];
        $form->onValidate[] = [$this, 'insertFormValidated'];

        return $form;
    }

    public function insertFormValidated(BootstrapUIForm $form)
    {
        $member = new MemberModel($this->database);
        $userExists = $member->getUserName($form->values->username);
        $emailExists = $member->getEmail($form->values->email);

        if (!$this->getPresenter()->template->member->users_roles->members_create) {
            $this->onSave('messages.members.PermissionDenied');
        }

        if (Validators::isEmail($form->values->email) === false) {
            $this->onSave('messages.members.invalidEmailFormat', false);
        } elseif ($emailExists > 0) {
            $this->onSave('messages.members.emailAlreadyExists', false);
        } elseif ($userExists > 0) {
            $this->onSave('messages.members.memberAlreadyExists', false);
        }
    }

    public function insertFormSucceeded(BootstrapUIForm $form)
    {
        $pwd = Random::generate(10);
        $pwdEncrypted = Passwords::hash($pwd);

        $userId = $this->database->table('users')
            ->insert(array(
                'email' => $form->values->email,
                'username' => $form->values->username,
                'password' => $pwdEncrypted,
                'date_created' => date('Y-m-d H:i:s'),
                'users_roles_id' => $form->values->role,
                'state' => 1,
            ));

        if ($form->values->sendmail) {
            $params = array(
                'username' => $form->values->username,
                'password' => $pwd
            );

            $helpdesk = new Helpdesk($this->database, $this->presenter->mailer);
            $helpdesk->setId(5);
            $helpdesk->setEmail($form->values->email);
            $helpdesk->setSettings($this->presenter->template->settings);
            $helpdesk->setParams($params);
            $helpdesk->send();
        }

        $this->onSave(false, $userId);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/InsertMemberControl.latte');
        $this->template->render();
    }

}
