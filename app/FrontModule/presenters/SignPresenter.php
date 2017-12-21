<?php

namespace App\FrontModule\Presenters;

use Caloriscz\Sign\ResetPassControl;
use Caloriscz\Sign\SignInControl;
use Caloriscz\Sign\SignUpControl;
use Caloriscz\Sign\VerifyAccountControl;
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
        $control->onSave[] = function($message) {
            if ($message) {
                $this->flashMessage($message, 'error');
            } else {
                $this->flashMessage('Informace o zapomenutém hesle odeslány', 'success');
            }

            $this->redirect(this);
        };

        return $control;
    }

    protected function createComponentResetPass()
    {
        $control = new ResetPassControl($this->database);
        return $control;
    }
    
    protected function createComponentVerify()
    {
        $control = new VerifyAccountControl($this->database);

        return $control;
    }

    protected function createComponentSignIn()
    {
        $control = new SignInControl($this->database);

        return $control;
    }

    protected function createComponentSignUp()
    {
        $control = new SignUpControl($this->database);
        $control->onSave[] = function ($redir, $message, $messageType) {

            $this->flashMessage($this->translator->translate($message), $messageType);
            $this->redirectUrl($redir);
        };

        return $control;
    }

    public function actionOut()
    {
        $this->getUser()->logout();

        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('in');
    }

    public function renderIn()
    {
        if ($this->getParameter('msg') == 1) {
            $this->template->msg = true;
        }
    }

    public function renderResetpass()
    {
        $activation = $this->database->table('users')->where(array(
            'email' => $this->getParameter('email'),
            'activation' => $this->getParameter('code'),
        ));

        if ($activation->count() > 0) {
            $this->template->activationValid = true;
        } else {
            $this->template->activationValid = false;
        }
    }

}