<?php

namespace App\FrontModule\Presenters;

use App\Model\IO;
use Caloriscz\Profile\ChangePasswordControl;
use Caloriscz\Profile\ChangePortraitControl;
use Caloriscz\Profile\EditAddressControl;
use Caloriscz\Profile\EditControl;
use Caloriscz\Profile\InsertAddressControl;
use Caloriscz\Profile\ProfileMenuControl;

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
        return new ChangePortraitControl($this->database);
    }

    protected function createComponentProfileMenu()
    {
        return new ProfileMenuControl($this->database);
    }

    protected function createComponentProfileChangePassword()
    {
        return new ChangePasswordControl($this->database);
    }

    protected function createComponentProfileEdit()
    {
        return new EditControl($this->database);
    }

    protected function createComponentProfileInsertAddress()
    {
        return new InsertAddressControl($this->database);
    }

    protected function createComponentProfileEditAddress()
    {
        return new EditAddressControl($this->database);
    }

    public function handleDeletePortrait()
    {
        $idfFolder = substr(__DIR__, 0, -27) . '/www';

        if (file_exists($idfFolder . '/images/profiles/portrait-' . $this->user->getId() . '.jpg')) {
            IO::remove($idfFolder . '/images/profiles/portrait-' . $this->user->getId() . '.jpg');
        }

        $this->redirect(this);
    }

    public function renderAddresses()
    {
        $this->template->addresses = $this->database->table('contacts')->where([
            'users_id' => $this->user->getId()
        ]);
    }

}
