<?php

namespace App\FrontModule\Presenters;

use App\Model\Document;

class ProfilePresenter extends \App\FrontModule\Presenters\BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === \Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('Byli jste odhlášeni');
            }

            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    protected function createComponentProfileChangePortrait()
    {
        $control = new \Caloriscz\Profile\ChangePortraitControl($this->database);
        return $control;
    }

    protected function createComponentProfileChangePassword()
    {
        $control = new \Caloriscz\Profile\ChangePasswordControl($this->database);
        return $control;
    }

    protected function createComponentProfileEdit()
    {
        $control = new \Caloriscz\Profile\EditControl($this->database);
        return $control;
    }

    protected function createComponentProfileInsertAddress()
    {
        $control = new \Caloriscz\Profile\InsertAddressControl($this->database);
        return $control;
    }

    protected function createComponentProfileEditAddress()
    {
        $control = new \Caloriscz\Profile\EditAddressControl($this->database);
        return $control;
    }

    function handleDeletePortrait()
    {
        $idfFolder = substr(__DIR__, 0, -27) . '/www';

        if (file_exists($idfFolder . "/images/profiles/portrait-" . $this->user->getId() . ".jpg")) {
            \App\Model\IO::remove($idfFolder . "/images/profiles/portrait-" . $this->user->getId() . ".jpg");
        }

        $this->redirect(this);
    }

    function renderAddresses()
    {
        $this->template->addresses = $this->database->table("contacts")->where(array(
            "users_id" => $this->user->getId(),
        ));
    }

}
