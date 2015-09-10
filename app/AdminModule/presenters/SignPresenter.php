<?php

namespace App\AdminModule\Presenters;

use Nette,
    Nette\Application\UI;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = '';

    protected function startup()
    {
        parent::startup();

        $this->template->signed = FALSE;
    }

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->addText('username')
                ->setAttribute("placeholder", 'Uživatel')
                ->setRequired('Vložte uživatelské jméno');

        $form->addPassword('password')
                ->setAttribute("placeholder", 'Heslo')
                ->setRequired('Vlože heslo.');

        $form->addSubmit('send', 'Přihlásit')
                ->setAttribute("class", "btn btn-lg btn-success btn-block");

        $form->onSuccess[] = $this->signInFormSucceeded;
        return $form;
    }

    public function signInFormSucceeded($form, $values)
    {

        try {
            $this->getUser()->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
            return;
        }

        $this->restoreRequest($this->backlink);
        $this->redirect(':Admin:Homepage:default');
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('in');
    }

}
