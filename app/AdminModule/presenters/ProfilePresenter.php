<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Import from files
 * @author Petr Karásek <caloris@caloris.cz>
 */
class ProfilePresenter extends BasePresenter
{
    protected function createComponentEditProfile()
    {
        $control = new \Caloriscz\Profile\Admin\EditControl($this->database);
        return $control;
    }
    
    /**
     * Verification form for invitations
     */
    function createComponentExportForm()
    {
        $form = $this->baseFormFactory->createUI();

        $form->addSubmit("submitm", "Export");

        $form->onSuccess[] = array($this, 'exportFormSucceeded');
        return $form;
    }

    /**
     * Image upload form
     */
    function createComponentUploadImageForm()
    {
        $form = $this->baseFormFactory->createUI();

        $form->addUpload("the_file", "Choose image");
        $form->addSubmit("submit", "Nahrát");

        $form->onSuccess[] = array($this, 'uploadImageFormSucceeded');
        return $form;
    }

    function uploadImageFormSucceeded()
    {
        if ($this->user->isLoggedIn()) {
            $uid = $this->template->member->uid;

            \App\Model\IO::directoryMake(substr(__DIR__, 0, -27) . "/www/users/" . $uid, 0755);

            if (file_exists(substr(__DIR__, 0, -27) . "/www/images/users/" . $uid . "/avatar.jpg")) {
                \App\Model\IO::remove(substr(__DIR__, 0, -27) . "/www/users/" . $uid . "/avatar.jpg");
                \App\Model\IO::upload(substr(__DIR__, 0, -27) . "/www/users/" . $uid, "avatar.jpg", 0644);
            } else {
                \App\Model\IO::upload(substr(__DIR__, 0, -27) . "/www/users/" . $uid, "avatar.jpg", 0644);
            }

            $image = \Nette\Utils\Image::fromFile(substr(__DIR__, 0, -27) . "/www/users/" . $uid . "/avatar.jpg");

            if ($image->width < $image->height) {
                $imageW = NULL;
                $imageH = 200;
                ;
            } else {
                $imageW = 200;
                $imageH = NULL;
            }

            $image->resize($imageW, $imageH, \Nette\Utils\Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save(substr(__DIR__, 0, -27) . "/www/users/" . $uid . "/avatar.jpg", 98, \Nette\Utils\Image::JPEG);
        }

        $this->redirect(":Admin:Profile:default", array("" => \Nette\Utils\Strings::random(8, "a-z0-9")));
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentChangePasswordForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addPassword("password1", "dictionary.main.Password");
        $form->addPassword("password2", "Opište heslo");
        $form->addSubmit('name', 'dictionary.main.Save');

        $form->onSuccess[] = $this->changePasswordFormSucceeded;
        return $form;
    }

    function changePasswordFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $ppwd = $form->values->password1;
        $ppwd2 = $form->values->password2;

        $passwordEncrypted = \Nette\Security\Passwords::hash($ppwd);

        if (strcasecmp($ppwd, $ppwd2) != 0) {
            $this->flashMessage($this->translator->translate('messages.sign.passwords-not-same'), 'error');
        }

        $this->database->table("users")->where(array(
            "id" => $this->user->getId(),
        ))->update(array(
            "password" => $passwordEncrypted,
        ));

        setcookie("calpwd", $passwordEncrypted, time() + time() + 60 * 60 * 24 * 30, "/");

        $this->redirect(':Admin:Profile:default', array("id" => null));
    }

    function handleDeletePortrait()
    {
        $idfFolder = substr(__DIR__, 0, -27) . '/www/users/' . $this->template->member->uid;

        \App\Model\IO::remove($idfFolder . "/avatar.jpg");

        $this->redirect(this);
    }

    function renderDefault()
    {
        
    }

}
