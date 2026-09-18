<?php

namespace App\Forms\Members;

use App\Security\PasswordReset;
use App\Model\MemberModel;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Forms\BootstrapUIForm;
use Nette\Forms\Form;
use Nette\Security\Passwords;
use Nette\Utils\Validators;

class InsertMemberControl extends Control
{

    public Explorer $database;
    public $onSave;

    public function __construct(Explorer $database)
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
        $form->addProtection('Platnost formuláře vypršela. Zkuste to znovu.');

        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addText('username', 'Uživatel')
            ->setRequired('Zadejte uživatelské jméno.')
            ->addRule(Form::MIN_LENGTH, 'Uživatelské jméno musí mít aspoň %d znaků', 3);
        $form->addEmail('email', 'E-mail')->setRequired('Zadejte e-mail.');

        if ($this->presenter->template->member->username === 'admin') {
            $form->addSelect('role', 'Uživatelská role', $roles)
                ->setHtmlAttribute('class', 'form-control');
        }

        $form->addCheckbox('sendmail', 'Odeslat odkaz pro nastavení hesla')->setValue(1);
        $form->addSubmit('submitm', 'Vytvořit')->setHtmlAttribute('class', 'btn btn-success');
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

        if (!$this->getPresenter()->template->memberRole || !$this->getPresenter()->template->memberRole->members) {
            $form->addError('Nemáte oprávnění');
        }

        if (Validators::isEmail($form->values->email) === false) {
            $form->addError('Zadejte platnou e-mailovou adresu');
        } elseif ($emailExists > 0) {
            $form->addError('E-mail již existuje');
        } elseif ($userExists > 0) {
            $form->addError('Uživatel již existuje');
        }
    }

    /**
     * @param BootstrapUIForm $form
     */
    public function insertFormSucceeded(BootstrapUIForm $form): void
    {
        // No usable password is disclosed; the owner chooses one through a link.
        $pwd = bin2hex(random_bytes(32));

        $passwordHash = new Passwords();
        $pwdEncrypted = $passwordHash->hash($pwd);

        $userId = $this->database->table('users')->insert([
            'email' => $form->values->email,
            'username' => $form->values->username,
            'password' => $pwdEncrypted,
            'date_created' => date('Y-m-d H:i:s'),
            'users_roles_id' => $this->presenter->template->member->username === 'admin'
                ? $form->values->role : null,
            'state' => 1,
        ]);

        if ($form->values->sendmail) {
            $sent = (new PasswordReset($this->database))->send((int) $userId->id,
                $this->presenter->mailer, $this->presenter->template->settings);
            if (!$sent) {
                $this->presenter->flashMessage('Uživatel byl vytvořen, ale odkaz se nepodařilo odeslat. Zkuste odeslání z detailu uživatele.', 'error');
            }
        }

        $this->onSave(false, $userId->id);
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/InsertMemberControl.latte');
        $this->template->render();
    }
}
