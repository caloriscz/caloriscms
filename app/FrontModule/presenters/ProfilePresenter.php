<?php

namespace App\FrontModule\Presenters;

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
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->addUpload("the_file", "Vyberte obrázek (nepovinné)");
        $form->addSubmit('submitm', 'Uložit');
        $form->onSuccess[] = $this->changePortraitFormSucceeded;

        return $form;
    }

    function changePortraitFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $membersDb = $this->database->table("users")->where(array("id" => $this->user->getId()));

        if ($membersDb->count() > 0) {
            $uid = $membersDb->fetch()->uid;

            \App\Model\IO::directoryMake(substr(__DIR__, 0, -27) . '/www/images/portraits/' . $uid, 0755);

            if (file_exists(substr(__DIR__, 0, -27) . '/www/images/portraits/' . $uid . "/portrait.jpg")) {
                \App\Model\IO::remove(substr(__DIR__, 0, -27) . '/www/images/portraits/' . $uid . "/portrait.jpg");
                \App\Model\IO::upload(substr(__DIR__, 0, -27) . '/www/images/portraits/' . $uid, "portrait.jpg", 0644);
            } else {
                \App\Model\IO::upload(substr(__DIR__, 0, -27) . '/www/images/portraits/' . $uid, "portrait.jpg", 0644);
            }
        }

        $this->redirect(":Front:Profile:foto", array("" => \Nette\Utils\Strings::random(8, "a-z0-9")));
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     * @return string
     */
    function createComponentChangePasswordForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->addPassword("password1", "Heslo");
        $form->addPassword("password2", "Znovu napište heslo");
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

        $this->redirect(':Front:Profile:heslo');
    }

    /**
     * Edit your profile
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addGroup("Osobní údaje");
        $form->addText("username", "Uživatel")
                ->setAttribute("style", "border: 0; font-size: 1.5em;")
                ->setDisabled();
        $form->addRadioList('sex', 'Pohlaví', array(
            1 => "\xC2\xA0" . 'žena',
            2 => "\xC2\xA0" . 'muž',
        ))->setAttribute("class", "checkboxlistChoose");
        $form->addGroup("Firemní údaje");
        $form->addText("ic", "IČ");
        $form->addText("company", "Společnost");

        $form->setDefaults(array(
            "name" => $this->template->member->name,
            "username" => $this->template->member->username,
        ));

        $form->addSubmit("submit", "Uložit");
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
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';

        $form->addHidden("id");
        $form->addHidden("contacts_id");
        $form->addText("name", "Name");
        $form->addText("street", "Street");
        $form->addText("zip", "ZIP");
        $form->addText("city", "city");

        $form->setDefaults(array(
            "contacts_group_id" => 2,
        ));

        $form->addSubmit('submitm', 'Uložit');
        $form->onSuccess[] = $this->insertAddressFormSucceeded;

        return $form;
    }

    function insertAddressFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        // create new contact
        $this->database->table("contacts")->insert(array(
            "contacts_groups_id" => 2,
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
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';

        $form->addHidden("id");
        $form->addHidden("contacts_id");
        $form->addText("name", "Name");
        $form->addText("street", "Address");
        $form->addText("zip", "ZIP");
        $form->addText("city", "city");

        $address = $this->database->table("orders_addresses")->get($this->getParameter("id"));

        $form->setDefaults(array(
            "id" => $address->id,
            "contacts_id" => $address->contacts->id,
            "name" => $address->contacts->name,
            "street" => $address->contacts->street,
            "zip" => $address->contacts->zip,
            "city" => $address->contacts->city,
        ));

        $form->addSubmit('submitm', 'Uložit');
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

    function handleDelete($id)
    {
        $idf = \Nette\Utils\Strings::padLeft($id, 5, "0");
        $idfFolder = substr(__DIR__, 0, -27) . '/www/images/products/MX' . $idf;

        $this->database->table("mimix_items")->get($id)->delete();

        \App\Model\IO::removeDirectory($idfFolder);

        $this->redirect(this);
    }

    function handleDeletePortrait()
    {
        $idf = \Nette\Utils\Strings::padLeft($this->user->getId(), 6, "0");
        $idfFolder = substr(__DIR__, 0, -27) . '/www/images/portraits/MU' . $idf;

        if (file_exists($idfFolder . "/portrait.jpg")) {
            \App\Model\IO::remove($idfFolder . "/portrait.jpg");
        }

        $this->redirect(this);
    }

    function handleUpdateMember($type)
    {
        if ($type == 4) {
            $typeX = 1;
        } else {
            $typeX = 4;
        }

        $this->database->table("users")->get($this->user->getId())->update(
                array(
                    "plan" => (int) $typeX
        ));

        $this->redirect(":Front:Profile:mojeClenstvi", array("" => \Nette\Utils\Random::generate(10)));
    }

    function renderAddresses()
    {
        $this->template->addresses = $this->database->table("contacts")->where(array(
            "users_id" => $this->user->getId(),
        ));
    }

}
