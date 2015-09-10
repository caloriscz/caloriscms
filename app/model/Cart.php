<?php

/*
 * Cart
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * Cart model
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Cart
{

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database, $user)
    {
        $this->database = $database;
        $this->user = $user;
    }

    /**
     * Get cart table
     */
    function getCart()
    {
        if ($this->user->isLoggedIn()) {
            $cartDb = $this->database->table("cart")->where(array("users_id" => $this->user->getId()));
        } else {
            $cartDb = $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]));
        }

        foreach ($cartDb as $cartA) {
            $id = $cartA->id;
        }

        $cartId = $this->database->table("cart")->get($id);

        return $cartId;
    }

    /**
     * Get addresses for billing and pazment
     */
    function getAddress()
    {
        $cart = $this->getCart();
        $cartAddresses = $cart->related('cart_addresses', 'cart_id');

        if ($cartAddresses->count() > 0) {
            foreach ($cartAddresses as $address) {
                if ($address->type == 1) {
                    $type = 'billing';
                } else {
                    $type = 'delivery';
                }

                $addresses[$type]["name"] = $address->contacts->name;
                $addresses[$type]["street"] = $address->contacts->street;
                $addresses[$type]["city"] = $address->contacts->city;
                $addresses[$type]["zip"] = $address->contacts->zip;
            }

        }

        return $addresses;
    }

    /**
     * Get cart items
     */
    function getItems()
    {
        $cart = $this->getCart();
        $cartItems = $cart->related('cart_items', 'cart_id');

        return $cartItems;
    }

    /**
     * Set Billing Address
     */
    function setBillingAddress($form)
    {
        $cart = $this->getCart();
        $cartItems = $cart->related('cart_addresses', 'cart_id')->where(array("type" => 1));

        if ($cartItems->count() > 0) {
            $cartItemsF = $cartItems->fetch();
            $contact = $this->database->table("contacts")
                    ->get($cartItemsF->contacts_id)
                    ->update(array(
                "contacts_groups_id" => 2,
                "users_id" => 0,
                "name" => $form->values->name,
                "street" => $form->values->street,
                "city" => $form->values->city,
                "zip" => $form->values->zip,
                "country" => "CZE",
                "email" => $form->values->email,
                "phone" => $form->values->phone,
            ));
        } else {
            $contact = $this->database->table("contacts")->insert(array(
                "contacts_groups_id" => 2,
                "users_id" => 0,
                "name" => $form->values->name,
                "street" => $form->values->street,
                "city" => $form->values->city,
                "zip" => $form->values->zip,
                "country" => "CZE",
                "email" => $form->values->email,
                "phone" => $form->values->phone,
            ));

            $this->database->table("cart_addresses")->insert(array(
                "cart_id" => $cart->id,
                "type" => 1,
                "contacts_id" => $contact,
            ));
        }
    }

    /**
     * Save delivery info
     * @param type $form
     */
    function setDeliveryAddress($form)
    {
        $cart = $this->getCart();
        $cartItems = $cart->related('cart_addresses', 'cart_id')->where(array("type" => 2));

        if ($cartItems->count() > 0) {
            $cartItemsF = $cartItems->fetch();
            $contact = $this->database->table("contacts")
                    ->get($cartItemsF->contacts_id)
                    ->update(array(
                "contacts_groups_id" => 2,
                "users_id" => 0,
                "name" => $form->values->del_name,
                "street" => $form->values->del_street,
                "city" => $form->values->del_city,
                "zip" => $form->values->del_zip,
                "country" => "CZE",
                "email" => $form->values->email,
                "phone" => $form->values->phone,
            ));
        } else {
            $contact = $this->database->table("contacts")->insert(array(
                "contacts_groups_id" => 2,
                "users_id" => 0,
                "name" => $form->values->del_name,
                "street" => $form->values->del_street,
                "city" => $form->values->del_city,
                "zip" => $form->values->del_zip,
                "country" => "CZE",
                "email" => $form->values->email,
                "phone" => $form->values->phone,
            ));

            $this->database->table("cart_addresses")->insert(array(
                "cart_id" => $cart->id,
                "type" => 2,
                "contacts_id" => $contact,
            ));
        }
    }

    function setInfo($form)
    {
        $cart = $this->getCart();
        $this->database->table("cart")->get($cart->id)
                ->update(array(
                    "note" => $form->values->note,
                    "email" => $form->values->email,
                    "phone" => $form->values->phone,
        ));
    }

}
