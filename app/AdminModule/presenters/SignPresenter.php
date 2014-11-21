<?php

namespace App\AdminModule\Presenters;

use Nette,
    Nette\Application\UI;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter {

    /** @persistent */
    public $backlink = '';

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->addText('username', 'Username:')
                ->setRequired('Vložte uživatelské jméno');

        $form->addPassword('password', 'Password:')
                ->setRequired('Vlože heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = $this->signInFormSucceeded;
        return $form;
    }

    public function signInFormSucceeded($form, $values) {
        try {
            $this->getUser()->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
            return;
        }

        $this->restoreRequest($this->backlink);
        $this->redirect('Homepage:');
    }

    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('in');
    }

}
