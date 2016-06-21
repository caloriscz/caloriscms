<?php

namespace App\AdminStoreModule\Presenters;

use Nette,
    App\Model;

/**
 * Store settings presenter.
 */
class SettingsPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = '';

    /**
     * VAT settings
     */
    function createComponentInsertVatForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addText("title", "dictionary.main.Title");
        $form->addText("vat", "dictionary.main.VAT");
        $form->addCheckbox("show", "dictionary.main.Show");
        $form->addSubmit('send', 'dictionary.main.Save');

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


        $this->redirect(":AdminStore:Settings:vats");
    }

    function handleDeleteVat($id)
    {
        $this->database->table("store_settings_vats")->get($id)->delete();

        $this->redirect(":AdminStore:Settings:vats", array("id" => null));
    }

    /**
     * Shipping settings
     */
    function createComponentInsertShippingForm()
    {
        $vats = $this->database->table("store_settings_vats")->where(array("show" => 1))
            ->order("title")->fetchPairs("id", "vat");

        $form = $this->baseFormFactory->createUI();
        $form->addText("title", "dictionary.main.Title");
        $form->addText("shipping", "Shipping");
        $form->addSelect("vat", "dictionary.main.VAT", $vats)
            ->setAttribute("class", "form-control");
        $form->addCheckbox("show", "dictionary.main.Show");
        $form->addSubmit('send', 'dictionary.main.Edit');

        $form->onSuccess[] = $this->insertShippingSucceeded;
        return $form;
    }

    function insertShippingSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_settings_shipping")
            ->insert(array(
                "title" => $form->values->title,
                "shipping" => $form->values->shipping,
                "store_settings_vats_id" => $form->values->vat,
                "show" => $form->values->show,
            ));

        $this->redirect(":AdminStore:Settings:shipping");
    }

    /**
     * Edit Shipping settings
     */
    function createComponentEditShippingForm()
    {
        $shipping = $this->database->table("store_settings_shipping")->get($this->getParameter("id"));
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addText("title", "dictionary.main.Title");
        $form->addText("shipping", "Základní cena");
        $form->addText("free_from", "Zdarma od");
        $form->addCheckbox("show", "dictionary.main.Show");
        $form->addSubmit('send', 'dictionary.main.Save');
        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "title" => $shipping->title,
            "shipping" => $shipping->shipping,
            "free_from" => $shipping->free_from,
            "show" => $shipping->show,
        ));

        $form->onSuccess[] = $this->editShippingSucceeded;
        return $form;
    }

    function editShippingSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_settings_shipping")
            ->get($form->values->id)
            ->update(array(
                "title" => $form->values->title,
                "shipping" => $form->values->shipping,
                "free_from" => $form->values->free_from,
                "show" => $form->values->show,
            ));

        $this->redirect(":AdminStore:Settings:shippingDetail", array("id" => $form->values->id));
    }

    function createComponentInsertShippingWeightForm()
    {
        $form = $this->baseFormFactory->createUI();
        $form->addHidden("shipping");
        $form->addText("upto", "Do kg");
        $form->addText("price", "dictionary.main.Price");
        $form->addSubmit('submitm', 'dictionary.main.Insert');

        $form->setDefaults(array("shipping" => $this->getParameter("id")));

        $form->onSuccess[] = $this->insertShippingWeightSucceeded;
        return $form;
    }

    function insertShippingWeightSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_settings_weights")
            ->insert(array(
                "store_settings_shipping_id" => $form->values->shipping,
                "upto" => $form->values->upto,
                "price" => $form->values->price,
            ));

        $this->redirect(":AdminStore:Settings:shippingDetail", array("id" => $form->values->shipping));
    }

    function handleDeleteShipping($id)
    {
        $orderShipping = $this->database->table("orders")->where(array("store_settings_shipping_id" => $id));

        if ($orderShipping->count() == 0) {
            $this->database->table("store_settings_shipping")->get($id)->delete();
        } else {
            $this->flashMessage($this->translator->translate('messages.sign.CannotBeDeletedUsedInOrders'), "error");
        }

        $this->redirect(":AdminStore:Settings:shipping");
    }

    function handleToggleShipping($id)
    {
        $orderShipping = $this->database->table("store_settings_shipping")->get($id);

        if ($orderShipping->show == 0) {
            $this->database->table("store_settings_shipping")->get($id)->update(array("show" => 1));
        } else {
            $this->database->table("store_settings_shipping")->get($id)->update(array("show" => 0));
        }

        $this->redirect(":AdminStore:Settings:shipping");
    }

    function handleTogglePayments($id)
    {
        $orderShipping = $this->database->table("store_settings_payments")->get($id);

        if ($orderShipping->show == 0) {
            $this->database->table("store_settings_payments")->get($id)->update(array("show" => 1));
        } else {
            $this->database->table("store_settings_payments")->get($id)->update(array("show" => 0));
        }

        $this->redirect(":AdminStore:Settings:payments");
    }

    /**
     * Shipping settings
     */
    function createComponentInsertPaymentForm()
    {
        $vats = $this->database->table("store_settings_vats")->where(array("show" => 1))
            ->order("title")->fetchPairs("id", "vat");

        $form = $this->baseFormFactory->createUI();
        $form->addText("title", "dictionary.main.Title");
        $form->addText("payment", "dictionary.main.Payment");
        $form->addSelect("vat", "dictionary.main.VAT", $vats)
            ->setAttribute("class", "form-control");
        $form->addCheckbox("show", "dictionary.main.Show");
        $form->addSubmit('send', 'dictionary.main.Save');

        $form->onSuccess[] = $this->insertPaymentSucceeded;
        return $form;
    }

    function insertPaymentSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("store_settings_payments")
            ->insert(array(
                "title" => $form->values->title,
                "payment" => $form->values->payment,
                "store_settings_vats_id" => $form->values->vat,
                "show" => $form->values->show,
            ));


        $this->redirect(":AdminStore:Settings:payments");
    }

    protected function createComponentShippingWeightGrid($name)
    {
        $grid = new \Ublaboo\DataGrid\DataGrid($this, $name);
        $grid->setDataSource($this->database->table("store_settings_weights")
            ->where("store_settings_shipping_id", $this->id));

        $grid->addGroupAction('Delete')->onSelect[] = [$this, 'handleDelete'];

        $grid->addColumnText('upto', 'Do kg')
            ->setRenderer(function ($item) {
                return "$item->upto g";
            })
            ->setSortable();
        $grid->addColumnText('price', 'dictionary.main.Price')
            ->setRenderer(function ($item) {
                return "$item->price " . $this->template->settings['store:currency:symbol'];
            })
            ->setSortable();

        $grid->getColumn('upto')->setAlign('right');
        $grid->getColumn('price')->setAlign('right');

        $grid->setTranslator($this->translator);
    }

    public function actionDelete()
    {
        $id = $this->getParameter('idm');
        $this->database->table("store_settings_weights")->where("id", $id)->delete();
        $this->flashMessage("Action '$this->action' done.", 'success');

        $this->restoreRequest($this->backlink);
        $this->redirect(':AdminStore:Settings:shippingDetail');
    }

    function handleDeletePayment($id)
    {
        $this->database->table("store_settings_payments")->get($id)->delete();

        $this->redirect(":AdminStore:Settings:payments");
    }

    public function renderDefault()
    {
        $this->template->settingsDb = $this->database
            ->table("settings")
            ->where("categories_id", 149);
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

    public function renderShippingDetail()
    {
        $this->template->shipping = $this->database->table("store_settings_shipping")
            ->get($this->getParameter("id"));
    }

    public function renderPayments()
    {
        $this->template->payments = $this->database
            ->table("store_settings_payments")
            ->order("title");
    }

}
