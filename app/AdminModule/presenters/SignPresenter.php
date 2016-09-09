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

    protected function createComponentLostPass()
    {
        $control = new \Caloriscz\Sign\LostPassControl($this->database);
        return $control;
    }

    protected function createComponentResetPass()
    {
        $control = new \Caloriscz\Sign\ResetPassControl($this->database);
        return $control;
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

        $role = $this->user->getRoles();
        $roleCheck = $this->database->table("users_roles")->get($role[0]);

        if ($roleCheck->admin_access == 0) {
            $this->flashMessage($this->translator->translate('messages.sign.no-access'), "error");
            $this->redirect(':Admin:Sign:in');
        } else {
            $this->database->table("users")->get($this->user->getId())->update(array("date_visited" => date("Y-m-d H:i:s")));
        }

        $this->restoreRequest($this->backlink);
        $this->redirect(':Admin:Homepage:default', array("id" => null));
    }



    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage($this->translator->translate('messages.sign.logged-out'), 'note');
        $this->redirect('in');

    }

    function renderResetpass()
    {
        $activation = $this->database->table("users")->where(array(
            "email" => $this->getParameter("email"),
            "activation" => $this->getParameter("code"),
        ));

        if ($activation->count() > 0) {
            $this->template->activationValid = TRUE;
        } else {
            $this->template->activationValid = FALSE;
        }
    }

}
