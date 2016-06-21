<?php

use Nette\Application\UI\Control;

class AddressControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Form: User fills in e-mail address to send e-mail with a password generator link
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->autocomplete = 'off';
        $form->addText("email")->addRule($form::EMAIL, 'dictionary.main.InsertEmail');
        $form->addText("username");
        $form->addText("name")->setRequired('dictionary.main.InsertName')
            ->addRule($form::MIN_LENGTH, 'Vložte jméno s nejméně %d znaky', 2);
        $form->addText("street")->addRule($form::MIN_LENGTH, 'Vložte jméno s nejvýše %d znaky', 4);
        $form->addText("city")->addRule($form::MIN_LENGTH, 'dictionary.main.InsertCity', 1);
        $form->addText("zip")->setRequired('dictionary.main.InsertZip');
        $form->addText("phone")->setRequired('dictionary.main.InsertPhone');
        $form->addText("company");
        $form->addText("vatin")->setOption("description", 1)
            ->addCondition($form::FILLED)
            ->addRule($form::MIN_LENGTH, 'dictionary.main.VatIn', 7);
        $form->addText("vatid")->addCondition($form::FILLED)->addRule($form::MIN_LENGTH, 'dictionary.main.VatId', 7);

        $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user, $this->presenter->template->isLoggedIn);
        $cartInfo = $cart->getCart();

        // Load implicit address for registered address
        if ($this->presenter->user->isLoggedIn() && $cartInfo->contacts_id == null) {
            $addressDb = $this->database->table("contacts")->where("users_id", $this->presenter->user->getId());

            if ($addressDb->count() > 0) {
                $address = $addressDb->fetch();
            } else {
                $address = null;
            }
        } else {
            $address = $this->database->table("contacts")->get($cartInfo->contacts_id);
        }

        $addressDelivery = $this->database->table("contacts")->get($cartInfo->delivery_contacts_id);

        if ($addressDelivery) {
            $delivery = 1;
            $addressDeliveryName = $addressDelivery->name;
            $addressDeliveryStreet = $addressDelivery->street;
            $addressDeliveryCity = $addressDelivery->city;
            $addressDeliveryZip = $addressDelivery->zip;
            $addressDeliveryPhone = $addressDelivery->phone;
            $addressDeliveryCompany = $addressDelivery->company;
        } else {
            $addressDeliveryName = null;
            $addressDeliveryStreet = null;
            $addressDeliveryCity = null;
            $addressDeliveryZip = null;
            $addressDeliveryPhone = null;
            $addressDeliveryCompany = null;
            $delivery = null;
        }

        $form->addText("delivery");
        $form->addText("del_name");
        $form->addText("del_company");
        $form->addText("del_street");
        $form->addText("del_city");
        $form->addText("del_zip");
        $form->addText("del_phone");
        $form->addTextArea("note");

        // Change address without javascript
        $form->addSubmit("loadBilling", "")->setValidationScope(FALSE)->onClick[] = $this->loadBillingAddressFormSucceeded;
        $form->addSubmit("loadDelivery", "")->setValidationScope(FALSE)->onClick[] = $this->loadDeliveryAddressFormSucceeded;

        if ($cartInfo->email) {
            $email = $cartInfo->email;
        } else {
            $email = $this->presenter->template->member->email;
        }

        $arr = array(
            "email" => $email,
            "name" => $address->name,
            "street" => $address->street,
            "city" => $address->city,
            "zip" => $address->zip,
            "phone" => $address->phone,
            "company" => $address->company,
            "vatin" => $address->vatin,
            "vatid" => $address->vatid,
            "del_name" => $addressDeliveryName,
            "del_street" => $addressDeliveryStreet,
            "del_city" => $addressDeliveryCity,
            "del_zip" => $addressDeliveryZip,
            "del_phone" => $addressDeliveryPhone,
            "del_company" => $addressDeliveryCompany,
            "delivery" => $delivery,
            "note" => $cartInfo->note,
        );

        $form->setDefaults(array_filter($arr));

        $form->addSubmit('submitm');
        $form->onSuccess[] = $this->editFormSucceeded;

        return $form;
    }

    /*
 * Load delivery address without javascript button
 */
    function loadBillingAddressFormSucceeded(Nette\Forms\Controls\SubmitButton $button)
    {
        $values = $button->form->getHttpData(\Nette\Forms\Form::DATA_TEXT);
        $addressId = $values["addresses"];

        $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user, $this->presenter->template->isLoggedIn);
        $cart->setBillingAddressById($addressId, $this->presenter->user->getId());

        $this->presenter->redirect(":Front:Order:address");
    }

    /*
     * Load delivery address without javascript button
     */
    function loadDeliveryAddressFormSucceeded(Nette\Forms\Controls\SubmitButton $button)
    {
        $values = $button->form->getHttpData(\Nette\Forms\Form::DATA_TEXT);
        $addressId = $values["addresses_del"];

        $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user, $this->presenter->template->isLoggedIn);
        $cart->setDeliveryAddressById($addressId, $this->presenter->user->getId());

        $this->presenter->redirect(":Front:Order:address");
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        if ($this->presenter->user->isLoggedIn()) {
            $emailUser = $this->presenter->template->member->email;
        } else {
            $emailUser = $form->values->email;
        }

        $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user, $this->presenter->template->isLoggedIn);

        $cart->setBillingAddress($form, $this->presenter->template->cartId, $form->values->addresses);

        if ($values["insertaddress"] == 2) {
            $cart->setDeliveryAddress($form, $this->presenter->template->cartId, $form->values->addresses_del);
        }


        $cart->setInfo($emailUser, $form->values->note, $form->values->phone);

        $this->presenter->redirect(":Front:Order:summary");
    }

    public function render()
    {
        $template = $this->template;

        if (isset($template->member)) {
            $template->member = $this->presenter->template->member;
        }

        $template->isLoggedIn = $this->presenter->template->isLoggedIn;

        $template->addresses = $this->database->table("contacts")
            ->where(array("users_id" => $this->presenter->user->getId()));

        foreach ($template->addresses as $item) {
            $addressSelect[$item->id] = $item->name . ', ' . $item->street . ', ' . $item->zip . ' ' . $item->city;
            $addressSelectJson[$item->id]['name'] = $item->name;
            $addressSelectJson[$item->id]['street'] = $item->street;
            $addressSelectJson[$item->id]['city'] = $item->city;
            $addressSelectJson[$item->id]['zip'] = $item->zip;
            $addressSelectJson[$item->id]['phone'] = $item->phone;
            $addressSelectJson[$item->id]['company'] = $item->company;
            $addressSelectJson[$item->id]['vatin'] = $item->vatin;
            $addressSelectJson[$item->id]['vatid'] = $item->vatid;
        }

        $template->addressesSelected = json_encode($addressSelectJson);

        $cartSelectDb = $this->database->table("orders")
            ->where(array("users_id" => $this->presenter->user->getId()));
        $cartSelect = $cartSelectDb->fetch();

        $template->cartId = $cartSelect->id;

        $cartSelectedDb = $this->database->table("orders")->where(array(
            "contacts_id" => $cartSelect->id
        ));
        $cartSelected = $cartSelectedDb->fetch();

        if ($cartSelectedDb->count() > 0) {
            $template->cartB = $cartSelected->contacts_id;
        }

        $cartSelectedDDb = $this->database->table("orders")
            ->where(array("contacts_id" => $cartSelect->id));
        $cartSelectedD = $cartSelectedDDb->fetch();

        if ($cartSelectedDb->count() > 0) {
            $template->cartD = $cartSelectedD->contacts_id;
        }

        $template->contacts = $this->database->table("contacts")
            ->where("users_id ?", $this->presenter->user->getId());

        $template->settings = $this->presenter->template->settings;

        $template->setFile(__DIR__ . '/AddressControl.latte');

        $template->render();
    }

}
