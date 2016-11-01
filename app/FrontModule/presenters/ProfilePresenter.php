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
<<<<<<< HEAD
        $control = new \Caloriscz\Profile\EditAddressControl($this->database);
        return $control;
=======
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
>>>>>>> master
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
