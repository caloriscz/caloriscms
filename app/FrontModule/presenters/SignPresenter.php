<?php

namespace App\FrontModule\Presenters;

use Nette,
    Nette\Application\UI;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = '';

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
    
    protected function createComponentVerify()
    {
        $control = new \Caloriscz\Sign\VerifyAccountControl($this->database);
        return $control;
    }

    protected function createComponentSignIn()
    {
        $control = new \Caloriscz\Sign\SignInControl($this->database);
        return $control;
    }

    protected function createComponentSignUp()
    {
        $control = new \Caloriscz\Sign\SignUpControl($this->database);
        return $control;
    }

    public function actionOut()
    {
        $oldid = session_id();

        $this->getUser()->logout();

        $newid = session_id();

        if ($this->template->settings['store:enabled']) {
            $this->database->table("orders")->where(array("uid" => $oldid))->update(array("uid" => $newid));
        }

        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('in');
    }

    function renderIn()
    {
        if ($this->getParameter('msg') == 1) {
            $this->template->msg = true;
        }
    }

    function renderResetpass()
    {
        $activation = $this->database->table("users")->where(array(
            "email" => $this->getParameter("email"),
            "activation" => $this->getParameter("code"),
        ));

        if ($activation->count() > 0) {
            $this->template->activationValid = true;
        } else {
            $this->template->activationValid = false;
        }
    }

}