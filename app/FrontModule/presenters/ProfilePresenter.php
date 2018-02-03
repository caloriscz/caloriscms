<?php

namespace App\FrontModule\Presenters;

use App\Forms\Profile\ChangePasswordControl;
use App\Forms\Profile\ChangePortraitControl;
use App\Forms\Profile\EditAddressControl;
use App\Forms\Profile\EditFrontProfileControl;
use App\Forms\Profile\InsertAddressControl;
use App\Model\IO;
use Caloriscz\Profile\ProfileMenuControl;
use Nette\Security\IUserStorage;

class ProfilePresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage('Byli jste odhlášeni');
            }

            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
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
        return new EditFrontProfileControl($this->database);
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

        $this->redirect('this');
    }

    public function renderAddresses()
    {
        $this->template->addresses = $this->database->table('contacts')->where([
            'users_id' => $this->user->getId()
        ]);
    }
}
