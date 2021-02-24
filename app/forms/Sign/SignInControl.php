<?php

namespace App\Forms\Sign;

use App\Model\MemberModel;
use Nette\Application\UI\Control;
use Nette\Database\Explorer;
use Nette\Database\SqlLiteral;
use Nette\Forms\BootstrapUIForm;
use Nette\Security\AuthenticationException;

class SignInControl extends Control
{

    public Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    protected function createComponentSignInForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';

        $form->addHidden('type');
        $form->addText('username', 'Uživatel')
            ->setRequired('Vložte uživatelské jméno.');

        $form->addPassword('password', 'Heslo')
            ->setRequired('Vložte heslo.');

        $form->addSubmit('send', 'Přihlásit se')
            ->setHtmlAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    /**
     * @param $form
     * @param $values
     * @throws \Nette\Application\AbortException
     */
    public function signInFormSucceeded($form, $values): void
    {
        try {
            $this->getPresenter()->getUser()->login($values->username, $values->password);

            if ($form->values->type === 'admin') {
                $role = $this->getPresenter()->user->getRoles();
                $roleCheck = $this->database->table('users_roles')->get($role[0]);

                if ($roleCheck && $roleCheck->sign === 0) {
                    $this->getPresenter()->flashMessage('Nemáte přístup', 'error');
                    $this->getPresenter()->redirect(':Admin:Sign:in');
                } else {
                    $this->database->table('users')->get($this->getPresenter()->user->getId())->update(['date_visited' => date('Y-m-d H:i:s')]);
                }
            }

            $this->database->table('users')->get($this->getPresenter()->user->getId())->update([
                'date_visited' => date('Y-m-d H:i:s'),
                'login_success' => new SqlLiteral('login_success + 1')
            ]);

            $this->getPresenter()->redirect(':Admin:Homepage:default');
        } catch (AuthenticationException $e) {
            $this->database->table('users')->where(['username' => $values->username])->update([
                'login_error' => new SqlLiteral('login_error')
            ]);

            $this->getPresenter()->flashMessage('Nesprávné heslo', 'error');
            $this->getPresenter()->redirect(':Admin:Sign:in');
        }
    }

    public function render(string $type = 'front')
    {
        $templateName = 'SignInControl';

        $this->template->type = $type;
        $this->template->setFile(__DIR__ . '/' . $templateName . '.latte');
        $this->template->render();
    }

}