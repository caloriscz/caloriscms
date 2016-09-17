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

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentChangePortraitForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addUpload("the_file", "Vyberte obrázek (nepovinné)");
        $form->addSubmit('submitm', 'dictionary.main.Insert');
        $form->onSuccess[] = $this->changePortraitFormSucceeded;

        return $form;
    }

    function changePortraitFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $membersDb = $this->database->table("users")->where(array("id" => $this->user->getId()));

        if ($membersDb->count() > 0) {
            $uid = $membersDb->fetch()->id;

            \App\Model\IO::directoryMake(substr(__DIR__, 0, -27) . '/www/portraits/', 0755);

            if (file_exists(substr(__DIR__, 0, -27) . '/www/images/profiles/portrait-' . $uid . '.jpg')) {
                \App\Model\IO::remove(substr(__DIR__, 0, -27) . '/www/images/profiles/portrait-' . $uid . '.jpg');
                \App\Model\IO::upload(substr(__DIR__, 0, -27) . '/www/images/profiles', 'portrait-' . $uid . '.jpg', 0644);
            } else {
                \App\Model\IO::upload(substr(__DIR__, 0, -27) . '/www/images/profiles', 'portrait-' . $uid . '.jpg', 0644);
            }
        }

        $this->redirect(":Front:Profile:image");
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

    /**
     * Edit your profile
     */
    function createComponentEditForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addGroup("Osobní údaje");
        $form->addText("username", "Uživatel")
            ->setAttribute("style", "border: 0; font-size: 1.5em;")
            ->setDisabled();
        $form->addRadioList('sex', 'Pohlaví', array(
            1 => "\xC2\xA0" . 'žena',
            2 => "\xC2\xA0" . 'muž',
        ))->setAttribute("class", "checkboxlistChoose");
        $form->addGroup("Firemní údaje");
        $form->addText("ic", "dictionary.main.VatId");
        $form->addText("company", "dictionary.main.Company");

        $form->setDefaults(array(
            "name" => $this->template->member->name,
            "username" => $this->template->member->username,
        ));

        $form->addSubmit("submit", "dictionary.main.Save");
        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("users")->where(array(
            "id" => $this->user->getId()
        ))
            ->update(array(
                "name" => $form->values->name,
                "address" => $form->values->address,
                "city" => $form->values->city,
                "zip" => $form->values->zip,
                "district" => $form->values->district,
                "ic" => $form->values->ic,
                "company" => $form->values->company,
                "sex" => $form->values->sex,
            ));

        $this->redirect(":Front:Profile:default");
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentInsertAddressForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addHidden("contacts_id");
        $form->addText("name", "dictionary.main.Name");
        $form->addText("street", "dictionary.main.Street");
        $form->addText("zip", "dictionary.main.ZIP");
        $form->addText("city", "dictionary.main.City");

        $form->setDefaults(array(
            "contacts_group_id" => 2,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save');
        $form->onSuccess[] = $this->insertAddressFormSucceeded;

        return $form;
    }

    function insertAddressFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $doc = new Document($this->database);
        $doc->setType(5);
        $doc->createSlug("contact-" . $form->values->name);
        $doc->setTitle($form->values->name);
        $page = $doc->create($this->user->getId());

        // create new contact
        $this->database->table("contacts")->insert(array(
            "categories_id" => 5,
            "pages_id" => $page,
            "users_id" => $this->user->getId(),
            "name" => $form->values->name,
            "street" => $form->values->street,
            "zip" => $form->values->zip,
            "city" => $form->values->city,
        ));

        $this->redirect(":Front:Profile:addresses");
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentEditAddressForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addHidden("contacts_id");
        $form->addText("name", "dictionary.main.Name");
        $form->addText("street", "dictionary.main.Street");
        $form->addText("zip", "dictionary.main.ZIP");
        $form->addText("city", "dictionary.main.City");

        $address = $this->database->table("contacts")->get($this->getParameter("id"));

        $form->setDefaults(array(
            "id" => $address->id,
            "name" => $address->name,
            "street" => $address->street,
            "zip" => $address->zip,
            "city" => $address->city,
        ));

        $form->addSubmit('submitm', 'dictionary.main.Save');
        $form->onSuccess[] = $this->editAddressFormSucceeded;

        return $form;
    }

    function editAddressFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")->where(array(
            "id" => $form->values->contacts_id,
        ))->update(array(
            "name" => $form->values->name,
            "street" => $form->values->street,
            "zip" => $form->values->zip,
            "city" => $form->values->city,
        ));

        $this->redirect(":Front:Profile:address", array("id" => $form->values->id));
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
