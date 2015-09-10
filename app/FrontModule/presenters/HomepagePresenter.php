<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            /* if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
              $this->flashMessage('Byli jste odhlášeni');
              }

              $this->redirect('Sign:in', array('backlink' => $this->storeRequest())); */
        }
    }

}
