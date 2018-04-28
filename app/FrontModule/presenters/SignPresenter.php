<?php

namespace App\FrontModule\Presenters;

use App\Forms\Sign\LostPassControl;
use App\Forms\Sign\ResetPassControl;
use App\Forms\Sign\SignInControl;
use App\Forms\Sign\SignUpControl;
use Caloriscz\Sign\VerifyAccountControl;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = '';

    /**
     * @return LostPassControl
     */
    protected function createComponentLostPass(): LostPassControl
    {
        $control = new LostPassControl($this->database);
        $control->onSave[] = function ($message) {
            if ($message) {
                $this->flashMessage($message, 'error');
            } else {
                $this->flashMessage('Informace o zapomenutém hesle odeslány', 'success');
            }

            $this->redirect(this);
        };

        return $control;
    }

    /**
     * @return ResetPassControl
     */
    protected function createComponentResetPass(): ResetPassControl
    {
        return new ResetPassControl($this->database);
    }

    /**
     * @return VerifyAccountControl
     */
    protected function createComponentVerify(): VerifyAccountControl
    {
        return new VerifyAccountControl($this->database);
    }

    /**
     * @return SignInControl
     */
    protected function createComponentSignIn(): SignInControl
    {
        return new SignInControl($this->database);
    }

    /**
     * @return SignUpControl
     */
    protected function createComponentSignUp(): SignUpControl
    {
        $control = new SignUpControl($this->database);
        $control->onSave[] = function ($redir, $message, $messageType) {

            $this->flashMessage($this->translator->translate($message), $messageType);
            $this->redirectUrl($redir);
        };

        return $control;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function actionOut(): void
    {
        $this->getUser()->logout();

        $this->flashMessage('Byli jste odhlášeni.');
        $this->redirect('in');
    }

    public function renderIn(): void
    {
        if ($this->getParameter('msg') === 1) {
            $this->template->msg = true;
        }
    }

    /**
     *
     */
    public function renderResetpass(): void
    {
        $activation = $this->database->table('users')->where([
            'email' => $this->getParameter('email'),
            'activation' => $this->getParameter('code')
        ]);

        if ($activation->count() > 0) {
            $this->template->activationValid = true;
        } else {
            $this->template->activationValid = false;
        }
    }

}