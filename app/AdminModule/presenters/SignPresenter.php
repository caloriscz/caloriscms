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

    protected function createComponentSignIn()
    {
        $control = new \Caloriscz\Sign\SignInControl($this->database);
        return $control;
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
