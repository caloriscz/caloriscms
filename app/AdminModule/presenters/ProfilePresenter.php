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

    public function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === \Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You were logged in');
            }

            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    /**
     * Verification form for invitations
     */
    function createComponentExportForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addSubmit("submitm", "Export");

        $form->onSuccess[] = array($this, 'exportFormSucceeded');
        return $form;
    }

    /**
     * Edit by user
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $members = $this->database->table("users")
                ->get($this->user->getId());

        $cols = array(
            "username" => $members->username,
            "email" => $members->email,
            "name" => $members->name,
        );

        $form->addGroup("Základní nastavení");
        $form->addText("name", "Name");

        $form->setDefaults(array(
            "name" => $cols["name"],
        ));

        $form->addSubmit("submit", "Uložit");
        $form->onSuccess[] = array($this, 'editFormSucceeded');
        return $form;
    }

    /**
     * Edit user settings by user
     * @global object $cols Table columns
     * @return array Redirect parameters
     */
    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("users")->get($this->user->getId())
                ->update(array(
                    "name" => $form->values->name,
        ));

        $this->redirect(this);
    }

    /**
     * Image upload form
     */
    function createComponentUploadImageForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->getElementPrototype()->class = 'form-horizontal';
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

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

            $image = \Nette\Image::fromFile(substr(__DIR__, 0, -27) . "/www/users/" . $uid . "/avatar.jpg");

            if ($image->width < $image->height) {
                $imageW = NULL;
                $imageH = 200;
                ;
            } else {
                $imageW = 200;
                $imageH = NULL;
            }

            $image->resize($imageW, $imageH, \Nette\Image::SHRINK_ONLY);
            $image->sharpen();
            $image->save(substr(__DIR__, 0, -27) . "/www/users/" . $uid . "/avatar.jpg", 98, \Nette\Image::JPEG);
        }

        $this->redirect(":Admin:Profile:default", array("" => \Nette\Utils\Strings::random(8, "a-z0-9")));
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentChangePasswordForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->addPassword("password1", "Heslo");
        $form->addPassword("password2", "Opište heslo");
        $form->addSubmit('name', 'Uložit');

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

        $this->redirect(':Admin:Profile:default');
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
