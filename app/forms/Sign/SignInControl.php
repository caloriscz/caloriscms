<?php

namespace App\Forms\Sign;

use App\Model\MemberModel;
use Nette\Application\UI\Control;
use Nette\Database\SqlLiteral;
use Nette\Forms\BootstrapUIForm;
use Nette\Security\AuthenticationException;

class SignInControl extends Control
{

    /** @var \Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    protected function createComponentSignInForm(): BootstrapUIForm
    {
        $form = new BootstrapUIForm();
        $form->setTranslator($this->getPresenter()->translator);
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('type');
        $form->addText('username', 'dictionary.main.User')
            ->setRequired('Vložte uživatelské jméno.');

        $form->addPassword('password', 'dictionary.main.Password')
            ->setRequired('Vložte heslo.');

        $form->addSubmit('send', 'dictionary.main.login')
            ->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    /**
     * @param $form
     * @param $values
     * @throws \Nette\Application\AbortException
     */
    public function signInFormSucceeded($form, $values)
    {
        $member = new MemberModel($this->database);
        $blocked = $member->getState($form->values->username);
        $typeUrl = 'Front';

        if ($blocked === false) {
            $this->getPresenter()->flashMessage('Musíte nejdříve ověřit váš účet', 'error');
            $this->getPresenter()->redirect(':Front:Sign:in');
        }

        if ($form->values->type === 'admin') {
            $typeUrl = 'Admin';
        }

        try {
            $this->getPresenter()->getUser()->login($values->username, $values->password);

            if ($form->values->type === 'admin') {
                $role = $this->getPresenter()->user->getRoles();
                $roleCheck = $this->database->table('users_roles')->get($role[0]);

                if ($roleCheck && $roleCheck->sign === 0) {
                    $this->getPresenter()->flashMessage($this->getPresenter()->translator->translate('messages.sign.no-access'), 'error');
                    $this->getPresenter()->redirect(':Admin:Sign:in');
                } else {
                    $this->database->table('users')->get($this->getPresenter()->user->getId())->update(['date_visited' => date('Y-m-d H:i:s')]);
                }
            }

            $this->database->table('users')->get($this->getPresenter()->user->getId())->update([
                'date_visited' => date('Y-m-d H:i:s'),
                'login_success' => new SqlLiteral('login_success + 1')
            ]);

            $this->getPresenter()->redirect(':' . $typeUrl . ':Homepage:default');
        } catch (AuthenticationException $e) {
            $this->database->table('users')->where(['username' => $values->username])->update([
                'login_error' => new SqlLiteral('login_error')
            ]);

            $this->getPresenter()->flashMessage('Nesprávné heslo', 'error');
            $this->getPresenter()->redirect(':' . $typeUrl . ':Sign:in');
        }
    }

    public function render($type = 'front')
    {
        $templateName = 'SignInAdminControl';

        if ($type === 'front') {
            $templateName = 'SignInFrontControl';
        }

        $this->template->type = $type;
        $this->template->setFile(__DIR__ . '/' . $templateName . '.latte');
        $this->template->render();
    }

}