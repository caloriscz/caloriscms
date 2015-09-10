<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Homepage presenter.
 */
class NewsletterPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('Byli jste odhlášeni.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    /**
     * Newsletter delete
     */
    function handleDelete($id)
    {
        $this->database->table("newsletter")->get($id)->delete();

        $this->redirect(":Admin:Newsletter:default");
    }

    public function renderDefault()
    {
        $this->template->newsletter = $this->database->table("newsletter")->order("email");
    }

}
