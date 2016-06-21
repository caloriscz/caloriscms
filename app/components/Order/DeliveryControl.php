<?php

use Nette\Application\UI\Control;

class DeliveryControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Choose shipping and payment request
     */
    function createComponentEditForm()
    {
        $form = new \Nette\Forms\BootstrapUIForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';
        $form->setMethod("GET");

        $form->addSubmit("submitm", "dictionary.main.Continue")
            ->setAttribute("class", "btn btn-cart-in btn-lg");

        $form->onSuccess[] = $this->editFormSucceeded;
        return $form;
    }

    function editFormSucceeded(\Nette\Forms\BootstrapUIForm $form)
    {
        $values = $form->getHttpData($form::DATA_TEXT); // get value from html input

        if ($values["pickup"]) {
            $pickup = $values["pickup"];
        } else {
            $pickup = null;
        }


        $this->database->table("orders")->where(array("uid" => session_id()))
            ->update(array(
                "store_settings_shipping_id" => $values["shipping"],
                "store_settings_payments_id" => $values["payment"],
                "pickups_contacts_id" => $pickup[$values["shipping"]],
            ));

        $this->presenter->redirect(":Front:Order:address");
    }

    public function render()
    {
        $template = $this->template;

        $template->cart = $this->database->table("orders_items")
            ->where(array("users_id" => $this->presenter->user->getId()))
            ->order("id");

        $template->cartInfo = $this->presenter->template->cartInfo;

        $pickups = $this->database->table("contacts")->where(array(
            "categories_id" => 46,
        ));

        $template->pickups = $pickups;

        $ulozenka = $this->database->table("contacts")->where(array(
            "categories_id" => 177,
        ));

        $template->ulozenka = $ulozenka;

        $template->shippingMethods = $this->database->table("store_settings_shipping")->where("show = 1");
        $template->paymentMethods = $this->database->table("store_settings_payments")->where("show = 1");

        $template->settings = $this->presenter->template->settings;

        $template->setFile(__DIR__ . '/DeliveryControl.latte');

        $template->render();
    }

}
