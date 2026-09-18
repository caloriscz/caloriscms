<?php

namespace App\AdminModule\Presenters;

use App\Forms\Sign\LostPassControl;
use App\Forms\Sign\ResetPassControl;
use App\Forms\Sign\SignInControl;
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
        // Protected forms are created while rendering; start before any output.
        $this->getSession()->start();
        $this->template->signed = false;
        $this->getHttpResponse()->setHeader('Cache-Control', 'no-store');
        $this->getHttpResponse()->setHeader('Referrer-Policy', 'no-referrer');
    }

    protected function createComponentResetPass(): ResetPassControl
    {
        return new ResetPassControl($this->database);
    }

    protected function createComponentLostPass(): LostPassControl
    {
        $control = new LostPassControl($this->database);
        $control->onSave[] = function ($message) {
            if ($message) {
                $this->flashMessage($message, 'error');
            } else {
                $this->flashMessage('Pokud lze pro tuto adresu obnovit přístup, obdržíte odkaz pro nastavení hesla.', 'success');
            }

            $this->redirect('this');
        };

        return $control;
    }

    /**
     * @return SignInControl
     */
    protected function createComponentSignIn(): SignInControl
    {
        return new SignInControl($this->database);
    }

    /**
     * Logs out user
     */
    public function actionOut(): void
    {
        $this->requireCsrfToken();
        $this->getUser()->logout();
        $this->flashMessage($this->translator->translate('Odhlášen'), 'note');
        $this->redirect('in');

    }

    public function renderResetpass(): void
    {
        // The credential arrives in the fragment, then in the protected POST.
        // Rendering (including email-link scanners) never consumes a link.
    }

}
