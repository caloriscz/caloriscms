<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

/**
 * Orders presenter.
 */
class StoreSettingsPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }
    }

    /**
     * Settings save
     */
    function createComponentEditSettingsForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden('setkey');
        $form->addText("setvalue", "Popisek:");
        $form->addSubmit('send', 'Upravit');

        $form->onSuccess[] = $this->editSettingsSucceeded;
        return $form;
    }

    function editSettingsSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        foreach ($values["set"] as $key => $value) {
            $this->database->table('settings')->where(array(
                        "setkey" => $key,
                    ))
                    ->update(array(
                        "setvalue" => $value,
            ));
        }

        $this->redirect(":Admin:StoreSettings:default");
    }

    /**
     * VAT settings
     */
    function createComponentInsertVatForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("title", "Title");
        $form->addText("vat", "VAT");
        $form->addCheckbox("show", "Show");
        $form->addSubmit('send', 'Upravit');

        $form->onSuccess[] = $this->insertVatSucceeded;
        return $form;
    }

    function insertVatSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_settings_vats")
                ->insert(array(
                    "title" => $form->values->title,
                    "vat" => $form->values->vat,
                    "show" => $form->values->show,
        ));


        $this->redirect(":Admin:StoreSettings:vats");
    }

    function handleDeleteVat($id)
    {
        $this->database->table("store_settings_vats")->get($id)->delete();

        $this->redirect(":Admin:StoreSettings:vats");
    }

    /**
     * Shipping settings
     */
    function createComponentInsertShippingForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("title", "Title");
        $form->addText("shipping", "Shipping");
        $form->addText("vat", "VAT");
        $form->addCheckbox("show", "Show");
        $form->addSubmit('send', 'Upravit');

        $form->onSuccess[] = $this->insertShippingSucceeded;
        return $form;
    }

    function insertShippingSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_settings_shipping")
                ->insert(array(
                    "title" => $form->values->title,
                    "shipping" => $form->values->shipping,
                    "vat" => $form->values->vat,
                    "show" => $form->values->show,
        ));


        $this->redirect(":Admin:StoreSettings:shipping");
    }

    function handleDeleteShipping($id)
    {
        $this->database->table("store_settings_shipping")->get($id)->delete();

        $this->redirect(":Admin:StoreSettings:shipping");
    }
    
    /**
     * Shipping settings
     */
    function createComponentInsertPaymentForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addText("title", "Title");
        $form->addText("payment", "Payment");
        $form->addText("vat", "VAT");
        $form->addCheckbox("show", "Show");
        $form->addSubmit('send', 'Upravit');

        $form->onSuccess[] = $this->insertPaymentSucceeded;
        return $form;
    }

    function insertPaymentSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_settings_payments")
                ->insert(array(
                    "title" => $form->values->title,
                    "payment" => $form->values->payment,
                    "vat" => $form->values->vat,
                    "show" => $form->values->show,
        ));


        $this->redirect(":Admin:StoreSettings:payments");
    }

    function handleDeletePayment($id)
    {
        $this->database->table("store_settings_payments")->get($id)->delete();

        $this->redirect(":Admin:StoreSettings:payments");
    }

    public function renderDefault()
    {
        $this->template->settingsDb = $this->database
                ->table("settings")
                ->where("prefix = ?", 'store');
    }

    public function renderVats()
    {
        $this->template->vats = $this->database
                ->table("store_settings_vats")
                ->order("title");
    }

    public function renderShipping()
    {
        $this->template->shipping = $this->database
                ->table("store_settings_shipping")
                ->order("title");
    }

    public function renderPayments()
    {
        $this->template->payments = $this->database
                ->table("store_settings_payments")
                ->order("title");
    }

}
