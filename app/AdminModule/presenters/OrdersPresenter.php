<?php

namespace App\AdminModule\Presenters;

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
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
        }

        $this->template->orderStates = $this->database->table("orders_states")->fetchPairs("id", "title");
        $this->template->shippingMethods = $this->database->table("store_settings_shipping")->fetchPairs("id", "title");
        $this->template->paymentMethods = $this->database->table("store_settings_payments")->fetchPairs("id", "title");
        $this->template->order = $this->database->table("orders")->get($this->getParameter("id"));
    }

    /**
     * Edit billing address request
     */
    public function createComponentEditBillingForm()
    {
        $order = $this->database->table("orders")->get($this->getParameter("id"));

        if ($order) {
            $orderBilling = $order->related("orders_addresses", "orders_id")->where("type = 1");
            $contacts = $this->database->table("contacts")->get($orderBilling->fetch()->id);
        }

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

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

        $this->redirect(":Admin:Orders:detail", array(
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
            $orderBilling = $order->related("orders_addresses", "orders_id")->where("type = 2");
            $contacts = $this->database->table("contacts")->get($orderBilling->fetch()->id);
        }


        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

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

        $this->redirect(":Admin:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Insert address request
     */
    function createComponentEditForm()
    {
        $order = $this->template->order;

        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("id");
        $form->addSelect("shipping", "Shipping Method", $this->template->shippingMethods)
                ->setAttribute("class", "form-control");
        $form->addSelect("payment", "Payment Method", $this->template->paymentMethods)
                ->setAttribute("class", "form-control");
        $form->addTextArea("note", "messages.helpdesk.message")
                ->setDisabled()
                ->setAttribute("placeholder", "messages.helpdesk.message")
                ->setAttribute("class", "form-control");
        $form->addTextArea("note_admin", "My message")
                ->setAttribute("placeholder", "My message")
                ->setAttribute("class", "form-control");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "note" => $order->note,
            "shipping" => $order->store_settings_shipping_id,
            "payment" => $order->store_settings_payments_id,
            "note_admin" => $order->note_admin,
        ));

        $form->addSubmit("submitm", "Save")
                ->setAttribute("class", "btn btn-success btn-lg");

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("orders")
                ->where(array("id" => $form->values->id))
                ->update(array(
                    "note_admin" => $form->values->note_admin,
                    "store_settings_shipping_id" => $form->values->shipping,
                    "store_settings_payments_id" => $form->values->payment,
                    ));

        $this->redirect(":Admin:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    /**
     * Change state
     */
    function createComponentChangeStateForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $orderStates = $this->template->orderStates;

        $form->addHidden("id");
        $form->addHidden("email");
        $form->addSelect("state", "messages.helpdesk.message", $orderStates)
                ->setAttribute("class", "form-control")
                ->setAttribute("placeholder", "messages.helpdesk.message");
        $form->addCheckbox("send", "Send information by e-mail");

        $form->setDefaults(array(
            "id" => $this->getParameter("id"),
            "state" => $this->template->order->state,
            "email" => $this->template->order->email,
        ));

        $form->addSubmit("submitm", "Save")
                ->setAttribute("class", "btn btn-success btn-lg");

        $form->onSuccess[] = $this->changeStateFormSucceeded;
        return $form;
    }

    function changeStateFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $this->database->table("orders")
                ->where(array("id" => $form->values->id))
                ->update(array("state" => $form->values->state));

        $templateLatte = APP_DIR . '/app/AdminModule/templates/StoreSettings/components/state-' . $form->values->state . '.latte';

        if ($form->values->send == "on" && file_exists($templateLatte)) {
            $latte = new \Latte\Engine;
            $params = array(
                'state' => $form->values->state,
            );

            $mail = new \Nette\Mail\Message;
            $mail->setFrom($this->template->settings["site_title"] .' <' . $this->template->settings["contact_email"] . '>')
                    ->addTo($form->values->email)
                    ->setHTMLBody($latte->renderToString($templateLatte, $params));

            $mailer = new \Nette\Mail\SendmailMailer;
            $mailer->send($mail);
        }

        $this->redirect(":Admin:Orders:detail", array(
            "id" => $form->values->id,
        ));
    }

    public function renderDefault()
    {
        $this->template->orders = $this->database->table("orders")->order("date_created DESC");
    }

}
