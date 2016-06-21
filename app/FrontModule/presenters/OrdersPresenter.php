<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Orders presenter.
 */
class OrdersPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('Byli jste odhlášeni', 'note');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }

        $this->template->orderStates = $this->database->table("orders_states")->fetchPairs("id", "title");
        $this->template->shippingMethods = $this->database->table("store_settings_shipping")->where("show = 1")->fetchPairs("id", "title");
        $this->template->paymentMethods = $this->database->table("store_settings_payments")->where("show = 1")->fetchPairs("id", "title");
        $this->template->order = $this->database->table("orders")->get($this->getParameter("id"));
    }

    /**
     * Edit billing address request
     */
    public function createComponentEditBillingForm()
    {
        $order = $this->database->table("orders")->get($this->getParameter("id"));

        if ($order) {
            $orderBilling = $order->related("orders", "contacts_id");
            $contacts = $this->database->table("contacts")->get($orderBilling->fetch()->id);
        }

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addHidden("contact_id");
        $form->addText("name", "messages.helpdesk.name")
                ->setAttribute("placeholder", "messages.helpdesk.name")
                ->setAttribute("class", "form-control");
        $form->addText("street", "Address")
                ->setAttribute("placeholder", "Address")
                ->setAttribute("class", "form-control");
        $form->addText("city", "City")
                ->setAttribute("placeholder", "City")
                ->setAttribute("class", "form-control");
        $form->addText("zip", "Address")
                ->setAttribute("placeholder", "ZIP")
                ->setAttribute("class", "form-control");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "contact_id" => $contacts->id,
            "name" => $contacts->name,
            "street" => $contacts->street,
            "city" => $contacts->city,
            "zip" => $contacts->zip,
        ));

        $form->addSubmit("submitm", "Save")
                ->setAttribute("class", "btn btn-success btn-lg");

        $form->onSuccess[] = $this->editBillingFormSucceeded;

        return $form;
    }

    public function editBillingFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")
                ->where(array("id" => $form->values->contact_id))
                ->update(array(
                    "name" => $form->values->name,
                    "street" => $form->values->street,
                    "city" => $form->values->city,
                    "zip" => $form->values->zip,
        ));

        $this->redirect(":Front:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Edit billing address request
     */
    function createComponentEditDeliveryAddressForm()
    {
        $order = $this->database->table("orders")->get($this->getParameter("id"));

        if ($order) {
            $orderBilling = $order->related("orders", "delivery_contacts_id");
            $contacts = $this->database->table("contacts")->get($orderBilling->fetch()->id);
        }

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addHidden("contact_id");
        $form->addText("name", "messages.helpdesk.name")
                ->setAttribute("placeholder", "messages.helpdesk.name")
                ->setAttribute("class", "form-control");
        $form->addText("company", "Společnost")
                ->setAttribute("placeholder", "Společnost")
                ->setAttribute("class", "form-control");
        $form->addText("street", "Address")
                ->setAttribute("placeholder", "Address")
                ->setAttribute("class", "form-control");
        $form->addText("city", "City")
                ->setAttribute("placeholder", "City")
                ->setAttribute("class", "form-control");
        $form->addText("zip", "Address")
                ->setAttribute("placeholder", "ZIP")
                ->setAttribute("class", "form-control");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "contact_id" => $contacts->id,
            "name" => $contacts->name,
            "street" => $contacts->street,
            "city" => $contacts->city,
            "zip" => $contacts->zip,
        ));

        $form->addSubmit("submitm", "Save")
                ->setAttribute("class", "btn btn-success btn-lg");

        $form->onSuccess[] = $this->editDeliveryFormSucceeded;
        return $form;
    }

    function editDeliveryFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("contacts")
                ->where(array("id" => $form->values->contact_id))
                ->update(array(
                    "name" => $form->values->name,
                    "street" => $form->values->street,
                    "city" => $form->values->city,
                    "zip" => $form->values->zip,
        ));

        $this->redirect(":Front:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Insert address request
     */
    function createComponentEditForm()
    {
        $order = $this->template->order;

        $form = $this->baseFormFactory->createUI();
        $form->addHidden("id");
        $form->addSelect("shipping", "Poštovné", $this->template->shippingMethods)
                ->setAttribute("class", "form-control");
        $form->addSelect("payment", "Platební metoda", $this->template->paymentMethods)
                ->setAttribute("class", "form-control");
        $form->addTextArea("note", "messages.helpdesk.message")
                ->setDisabled()
                ->setAttribute("placeholder", "messages.helpdesk.message")
                ->setAttribute("class", "form-control");
        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "note" => $order->note,
            "shipping" => $order->store_settings_shipping_id,
            "payment" => $order->store_settings_payments_id,
            "note_admin" => $order->note_admin,
        ));

        $form->addSubmit("submitm", "dictionary.main.Save")
                ->setAttribute("class", "btn btn-success btn-lg");

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("orders")
                ->where(array("id" => $form->values->id))
                ->update(array(
                    "store_settings_shipping_id" => $form->values->shipping,
                    "store_settings_payments_id" => $form->values->payment,
        ));

        $this->redirect(":Front:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    public function renderDefault()
    {
        $this->template->orders = $this->database->table("orders")->order("date_created DESC");
    }

    public function renderDetail()
    {
        $order = $this->database->table("orders")->get($this->getParameter("id"));

        if ($order) {
            $orderBilling = $order->related("orders", "contacts_id");

            $contactsB = $this->database->table("contacts")->get($orderBilling->fetch()->contacts_id);
        }

        $orderD = $this->database->table("orders")->get($this->getParameter("id"));

        if ($orderD) {
            $orderDelivery = $orderD->related("orders", "contacts_id");

            $contactsD = $this->database->table("contacts")->get($orderDelivery->fetch()->contacts_id);
        }

        $this->template->contactsB = $contactsB;
        $this->template->contactsD = $contactsD;
    }

}
