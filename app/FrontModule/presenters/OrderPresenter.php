<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Cart presenter.
 */
class OrderPresenter extends BasePresenter
{

    protected function startup()
    {
        parent::startup();

        $this->template->cartInfo = $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]))->fetch();
    }

    /**
     * Choose shipping and payment request
     */
    function createComponentChooseShippingForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal test";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $pickups = $this->database->table("contacts")->where(array(
                    "contacts_groups_id" => 3,
                    "NOT name = " => 'main',
                ))
                ->fetchPairs("id", "name");

        $pickup = $this->database->table("cart")->where(array(
            "uid" => $_COOKIE["PHPSESSID"],
        ));

        $form->addSelect("pickup", "Pickup", $pickups)
                ->setAttribute("class", "form-control")
                ->setAttribute("style", "max-width: 300px;");
        $pickupDb = $pickup->fetch()->contacts_id;

        if ($pickup->count() > 0 && $pickupDb != 12) {
            echo $pickup->fetch()->contacts_id;
            $form->setDefaults(array(
                "pickup" => $pickupDb,
            ));
        }

        $form->addSubmit("submitm", ("dictionary.main.continue"))
                ->setAttribute("class", "btn btn-success btn-lg");

        $form->onSuccess[] = $this->chooseShippingFormSucceeded;
        return $form;
    }

    function chooseShippingFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]))
                ->update(array(
                    "store_settings_shipping_id" => $values["shipping"],
                    "store_settings_payments_id" => $values["payment"],
                    "contacts_id" => $values["pickup"],
        ));

        $this->redirect(":Front:Order:address", array(
            "shipping" => $values["shipping"],
            "payment" => $values["payment"],
        ));
    }

    /**
     * Insert address request
     */
    function createComponentInsertAddressForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';


        if ($this->user->isLoggedIn() == FALSE) { // registered do not need to fill empty fields
            $form->addText("name", "messages.helpdesk.name")
                    ->setAttribute("placeholder", "dictionary.main.name")
                    ->setAttribute("class", "form-control")
                    ->addRule($form::MIN_LENGTH, 'Enter name with at least %d symbols', 2);
            $form->addText("street", "Address")
                    ->setAttribute("placeholder", "Address")
                    ->setAttribute("class", "form-control")
                    ->addRule($form::MIN_LENGTH, 'Enter address with at least %d symbols', 4);
            $form->addText("city", "City")
                    ->setAttribute("placeholder", "City")
                    ->setAttribute("class", "form-control")
                    ->addRule($form::MIN_LENGTH, 'Enter valid name of the city', 1);
            $form->addText("zip", "Address")
                    ->setAttribute("placeholder", "ZIP")
                    ->setAttribute("class", "form-control")
                    ->addRule($form::MIN_LENGTH, 'Enter valid ZIP', 5);
        }

        $form->addText("email", "dictionary.main.email")
                ->addRule($form::EMAIL, 'Incorrect email address')
                ->setAttribute("placeholder", "dictionary.main.email")
                ->setAttribute("class", "form-control")
                ->setOption("description", 1);
        $form->addText("phone", "dictionary.main.phone")
                ->setAttribute("placeholder", "dictionary.main.phone")
                ->setAttribute("class", "form-control")
                ->setOption("description", 1)
                ->addCondition($form::FILLED)
                ->addRule($form::MIN_LENGTH, 'Enter valid phone number', 5);
        $form->addTextArea("note", "dictionary.main.message")
                ->setAttribute("placeholder", "dictionary.main.message")
                ->setAttribute("class", "form-control");
        $form->addText("del_name", "dictionary.main.name")
                ->setAttribute("placeholder", "dictionary.main.name")
                ->setAttribute("class", "form-control");
        $form->addText("del_street", "dictionary.main.street")
                ->setAttribute("placeholder", "dictionary.main.street")
                ->setAttribute("class", "form-control");
        $form->addText("del_city", "dictionary.main.city")
                ->setAttribute("placeholder", "dictionary.main.city")
                ->setAttribute("class", "form-control");
        $form->addText("del_zip", "dictionary.main.ZIP")
                ->setAttribute("placeholder", "dictionary.main.ZIP")
                ->setAttribute("class", "form-control");

        $cart = new Model\Cart($this->database, $this->user);
        $cartInfo = $cart->getCart();
        $addresses = $cart->getAddress();

        $form->setDefaults(array(
            "name" => $addresses["billing"]["name"],
            "street" => $addresses["billing"]["street"],
            "city" => $addresses["billing"]["city"],
            "zip" => $addresses["billing"]["zip"],
            "del_name" => $addresses["delivery"]["name"],
            "del_street" => $addresses["delivery"]["street"],
            "del_zip" => $addresses["delivery"]["zip"],
            "del_city" => $addresses["delivery"]["city"],
            "email" => $cartInfo->email,
            "phone" => $cartInfo->phone,
            "note" => $cartInfo->note,
        ));

        $form->addSubmit("submitm", "dictionary.main.continue")
                ->setAttribute("class", "btn btn-success btn-lg");

        $form->onSuccess[] = $this->insertAddressFormSucceeded;
        return $form;
    }

    function insertAddressFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $cart = new Model\Cart($this->database, $this->user);

        // Check if some address were chosen
        $billingDb = $this->database->table("cart_addresses")->where(array(
            "cart_id" => $cart->getCart(),
            "type" => 1,
        ));

        if ($billingDb->count() == 0) {
            $this->flashMessage("Choose address", "error");
            $this->redirect(":Front:Order:address");
        }

        if (!$this->user->isLoggedIn()) {
            $cart->setBillingAddress($form);

            if ($form->values->del_name && $form->values->del_street && $form->values->del_city) {
                $cart->setDeliveryAddress($form);
            }
        }

        $cart->setInfo($form);

        $this->redirect(":Front:Order:summary");
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentInsertNewAddressForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';

        $form->addHidden("id");
        $form->addHidden("contacts_id");
        $form->addText("name", "dictionary.main.name");
        $form->addText("street", "dictionary.main.street");
        $form->addText("zip", "dictionary.main.ZIP");
        $form->addText("city", "dictionary.main.city");

        $form->setDefaults(array(
            "contacts_group_id" => 2,
        ));

        $form->addSubmit('submitm', 'Uložit');
        $form->onSuccess[] = $this->insertNewAddressFormSucceeded;

        return $form;
    }

    function insertNewAddressFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
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

        $this->redirect(":Front:Order:address");
    }

    /**
     * Finish order request
     */
    function createComponentFinishOrderForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addCheckbox('agree')
                ->addCondition($form::EQUAL, TRUE)
                ->toggle('agree-container');
        $form->addSubmit("submitm", "Finish the order")
                ->setAttribute("class", "btn btn-success btn-lg");

        $form->onSuccess[] = $this->finishOrderFormSucceeded;
        return $form;
    }

    function finishOrderFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        if ($form->values->agree == FALSE) {
            $this->flashMessage("You nee to agree with terms in order to successfuly finish the order", "error");
            $this->redirect(":Front:Order:summary");
        }

        // Select order from cart
        if ($this->user->isLoggedIn() == FALSE) {
            $cartDb = $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]));
        } else {
            $cartDb = $this->database->table("cart")->where(array("users_id" => $this->user->getId()));
        }

        if ($cartDb->count() == 0) {
            $this->flashMessage("Oops! There's nothing in cart", "error");
            $this->redirect(":Front:Order:summary");
        }

        $cart = $cartDb->fetch();

        // Create order
        $oid = $this->database->table("orders")->insert(array(
            "users_id" => $cart->users_id,
            "email" => $cart->users->email,
            "store_settings_shipping_id" => $cart->store_settings_shipping_id,
            "store_settings_payments_id" => $cart->store_settings_payments_id,
            "state" => 1,
            "date_created" => date("Y-m-d H:i:s"),
            "note" => $cart->note,
        ));

        // Unique identifier
        $this->database->table("orders")->get($oid)->update(array(
            "oid" => 'IV' . Nette\Utils\Strings::padLeft($oid, 6, 0),
        ));

        // TODO: Create order items

        $cartItems = $cart->related("cart_items", "cart_id");

        foreach ($cartItems as $item) {
            $this->database->table("orders_items")->insert(array(
                "orders_id" => $oid,
                "store_id" => $item->store_id,
                "store_stock_id" => $item->store_stock_id,
                "amount" => $item->amount,
                "date_created" => $item->date_created,
            ));
        }

        // Cart to store addresses
        $cartAddresses = $cart->related("cart_addresses", "cart_id");

        foreach ($cartAddresses as $address) {
            $this->database->table("orders_addresses")->insert(array(
                "orders_id" => $oid,
                "type" => $address->type,
                "contacts_id" => $address->contacts->id,
            ));
        }

        // Clean cart, cart_addresses and cart_items (save carts as emptied carts?)
        $this->database->table("cart")->get($cart->id)->delete();

        // Send e-mail to user with pending state
        $latte = new \Latte\Engine;
        $params = array(
            'oid' => $oid,
        );

        $mail = new \Nette\Mail\Message;
        $mail->setFrom($this->template->settings["contact_email"]);
        $mail->addTo($cart->email);
        $mail->setSubject("Order: Your order");
        $mail->setHTMLBody($latte->renderToString(substr(APP_DIR, 0, -4) . '/app/AdminModule/templates/StoreSettings/components/state-2.latte', $params));

        $mailer = new \Nette\Mail\SendmailMailer;
        $mailer->send($mail);

        // TODO: Send e-mail to admin with link to administration

        $this->flashMessage("Order finished", "success");
        $this->redirect(":Front:Order:success");
    }

    /**
     * Change billing address
     */
    function handleChangeAddress($id)
    {
        $addressExists = $this->database->table("cart_addresses")
                ->where(array("cart_id" => $this->getParameter("cart"), "type" => $this->getParameter("type")));

        if ($addressExists->count() > 0) {
            $this->database->table("cart_addresses")
                    ->where(array("cart_id" => $this->getParameter("cart"), "type" => $this->getParameter("type")))
                    ->update(array("contacts_id" => $id));
        } else {
            // TODO: Address was not established for registered user - make form for registered user only
            $this->database->table("cart_addresses")->insert(array(
                "contacts_id" => $id,
                "cart_id" => $this->getParameter("cart"),
                "type" => $this->getParameter("type"),
            ));
        }

        $this->redirect(":Front:Order:address");
    }

    function renderDefault()
    {
        $this->template->cart = $this->database->table("cart_items")
                ->where(array("users_id" => $this->user->getId()))
                ->order("id");

        $this->template->shippingMethods = $this->database->table("store_settings_shipping");
        $this->template->paymentMethods = $this->database->table("store_settings_payments");
    }

    function renderAddress()
    {
        $cartSelectDb = $this->database->table("cart")
                ->where(array("users_id" => $this->user->getId()));
        $cartSelect = $cartSelectDb->fetch();

        $this->template->cartId = $cartSelect->id;

        $cartSelectedDb = $this->database->table("cart_addresses")
                ->where(array("cart_id" => $cartSelect->id, "type" => 1));
        $cartSelected = $cartSelectedDb->fetch();

        if ($cartSelectedDb->count() > 0) {
            $this->template->cartB = $cartSelected->contacts_id;
        }

        $cartSelectedDDb = $this->database->table("cart_addresses")
                ->where(array("cart_id" => $cartSelect->id, "type" => 2));
        $cartSelectedD = $cartSelectedDDb->fetch();

        if ($cartSelectedDb->count() > 0) {
            $this->template->cartD = $cartSelectedD->contacts_id;
        }

        $this->template->contacts = $this->database->table("contacts")
                ->where("users_id ?", $this->user->getId());
    }

    function renderSummary()
    {
        $cart = new Model\Cart($this->database, $this->user);

        if ($this->user->isLoggedIn()) {
            $cartDb = $this->database->table("cart")->where(array("users_id" => $this->user->getId()));

            if ($cartDb->count() > 0) {
                $this->template->cart = $cartDb->fetch();
            }

            // Get billing cart address
            $address = $this->database->table("cart")
                    ->get($this->template->cart->id);
            $this->template->address = $address->related("contacts", "users_id");

            $cartSelectedBDb = $address->related("cart_addresses", "cart_id")->where("type = 1");

            if ($cartSelectedBDb->count() > 0) {
                $this->template->cartB = $cartSelectedBDb->fetch()->contacts_id;
            }
        } else {
            
        }

        $this->template->info = $cart->getCart();
        $this->template->address = $cart->getAddress();
        $this->template->items = $cart->getItems();
    }

}
