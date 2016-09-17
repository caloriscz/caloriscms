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

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     * @return string
     */
    function createComponentChangePasswordForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addPassword("password1", "dicitionary.main.Password");
        $form->addPassword("password2", "Znovu napište heslo");
        $form->addSubmit('name', 'dictionary.main.Change');

        $form->onSuccess[] = $this->changePasswordFormSucceeded;
        return $form;
    }

    function changePasswordFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $ppwd = $form->values->password1;
        $ppwd2 = $form->values->password2;

        $passwordEncrypted = \Nette\Security\Passwords::hash($ppwd);

        if (strcasecmp($ppwd, $ppwd2) != 0) {
            $this->flashMessage('Hesla se neshodují');
        }

        $this->database->table("users")->where(array(
            "id" => $this->user->getId(),
        ))->update(array(
            "password" => $passwordEncrypted,
        ));

        setcookie("calpwd", $passwordEncrypted, time() + time() + 60 * 60 * 24 * 30, "/");

        $this->redirect(':Front:Profile:heslo');
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
