<?php

namespace App\AdminModule\Presenters;

use App\Forms\Sign\LostPassControl;
use App\Forms\Sign\ResetPassControl;
use App\Forms\Sign\SignInControl;
use App\Model\Logger;
use Nette;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{
    /**
     * @throws Nette\Application\AbortException
     */
    protected function startup()
    {
        parent::startup();
        $this->template->signed = FALSE;
    }

    protected function createComponentResetPass()
    {
        return new ResetPassControl($this->database);
    }

    protected function createComponentLostPass()
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

        $logger = new Logger($this->database);
        $logger->setEvent('Uživatel v administraci si nechal zaslat heslo');
        $logger->setDescription("");
        $logger->setUser($this->user->getId());
        $logger->save();

        return $control;
    }

    protected function createComponentSignIn()
    {
        $control = new SignInControl($this->database);

        $logger = new Logger($this->database);
        $logger->setEvent('Uživatel přihlášen');
        $logger->setDescription('');
        $logger->setUser($this->user->getId());
        $logger->save();

        return $control;
    }

    /**
     * Logs out user
     */
    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage($this->translator->translate('messages.sign.logged-out'), 'note');
        $this->redirect('in');

    }

    public function renderResetpass()
    {
        $this->template->activationValid = false;

        $activation = $this->database->table('users')->where([
            'email' => $this->getParameter('email'),
            'activation' => $this->getParameter('code')
        ]);

        if ($activation->count() > 0) {
            $this->template->activationValid = true;
        }
    }

}
