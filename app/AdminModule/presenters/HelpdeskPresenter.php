<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class HelpdeskPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    function handleDelete($id)
    {
        $this->database->table("helpdesk")->get($id)->delete();

        $this->redirect(":Admin:Helpdesk:default");
    }

    public function renderDefault()
    {
        $this->template->helpdesk = $this->database->table("helpdesk")->order("subject");
    }

}
