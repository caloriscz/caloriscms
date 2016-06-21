<?php

/*
 * Cart
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model\Store;

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
        $cartDb = $this->database->table("orders")->where(array(
            "uid" => session_id(),
            "orders_states_id" => null,
        ));

        if ($cartDb->count() > 0) {
            return $cartDb->fetch();
        } else {
            return FALSE;
        }
    }

    /**
     * Get cart identification
     */
    function getCartId()
    {
        $cart = $this->getCart();

        if ($cart) {
            $cartId = $cart->id;
        } else {
            $cartId = FALSE;
        }

        return $cartId;
    }

    /**
     * Get total price of cart
     */
    function getCartTotal($settings)
    {
        $cart = $this->getCart();

        if ($cart) {
            if ($settings['store:order:isVatIncluded']) {
                $cartTotal = $cart->related("orders_items", "orders_id")->sum('price * amount');
            } else {
                //$cartTotal = $cart->related("orders_items", "orders_id")->sum('(price * (1+(store_settings_vats.vat/100))) * amount'); // už s DPH
                $cartTotal = $cart->related("orders_items", "orders_id")->sum('price * amount');
            }
        }

        return $cartTotal;
    }

    function getAddress($id)
    {
        $address = $this->database->table('contacts')->get($id);

        return $address;
    }

    /**
     * Get billing address id
     */
    function getAddressId($cartId, $type = 1)
    {
        $addressDb = $this->database->table("orders")->get($cartId);

        if ($addressDb > 0) {
            if ($type == 1) {
                return $addressDb->contacts_id;
            } else {
                return $addressDb->delivery_contacts_id;
            }
        } else {
            return null;
        }
    }

    function getItems()
    {
        $cart = $this->getCart();

        if ($cart) {
            $cartItems = $cart->related('orders_items', 'orders_id');
        } else {
            $cartItems = FALSE;
        }

        return $cartItems;
    }

    /**
     * Get numberof cart items
     */
    function getCartItems()
    {
        $cart = $this->getCart();

        if ($cart) {
            $cartTotal = $cart->related("orders_items", "orders_id")->sum('amount');

            if (!$cartTotal) {
                $cartTotal = 0;
            }
        } else {
            $cartTotal = 0;
        }

        return $cartTotal;
    }

    /**
     * Set Billing Address
     */
    function setBillingAddress($form, $cartId, $addressPreId = '')
    {
        if ($addressPreId == '') {
            $addressArr = array(
                "users_id" => $this->user->getId(),
                "name" => $form->values->name,
                "street" => $form->values->street,
                "city" => $form->values->city,
                "zip" => $form->values->zip,
                "company" => $form->values->company,
                "phone" => $form->values->phone,
                "vatin" => $form->values->vatin,
                "vatid" => $form->values->vatid,
            );

            $addressIdPost = array_diff($addressArr, array(''));
            $isAddressRegisteredDb = $this->database->table("contacts")->where($addressIdPost);

            if ($isAddressRegisteredDb->count() > 0) {
                $addressId = $isAddressRegisteredDb->fetch()->id;
            } else {
                $doc = new \App\Model\Document($this->database);
                $doc->setType(5);
                $doc->createSlug("contact-" . $form->values->name);
                $doc->setTitle($form->values->name);
                $page = $doc->create($this->user->getId());
                \App\Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page, 0755);
                $addressArr["pages_id"] = $page;

                $addressId = $this->database->table("contacts")->insert($addressArr);
            }
        } else {
            $addressId = $addressPreId;
        }

        $this->database->table("orders")->get($cartId)->update(array(
            "contacts_id" => $addressId,
        ));

    }

    function setBillingAddressById($id, $user_id)
    {
        $this->database->table("orders")->where("uid = ? AND users_id = ?", session_id(), $user_id)
            ->update(array("contacts_id" => $id));
    }

    function setDeliveryAddressById($id, $user_id)
    {
        $this->database->table("orders")->where("uid = ? AND users_id = ?", session_id(), $user_id)
            ->update(array("delivery_contacts_id" => $id));
    }

    /**
     * Save delivery info
     * @param type $form
     */
    function setDeliveryAddress($form, $cartId, $addressPreId = '')
    {
        if (!$addressPreId) {
            $addressArr = array(
                "users_id" => $this->user->getId(),
                "name" => $form->values->del_name,
                "street" => $form->values->del_street,
                "city" => $form->values->del_city,
                "zip" => $form->values->del_zip,
                "company" => $form->values->del_company,
            );

            $addressIdPost = array_diff($addressArr, array(''));
            $isAddressRegisteredDb = $this->database->table("contacts")->where($addressIdPost);

            if ($isAddressRegisteredDb->count() > 0) {
                $addressId = $isAddressRegisteredDb->fetch()->id;
            } else {
                $doc = new \App\Model\Document($this->database);
                $doc->setType(5);
                $doc->createSlug("contact-" . $form->values->name);
                $doc->setTitle($form->values->name);
                $page = $doc->create($this->user->getId());
                \App\Model\IO::directoryMake(substr(APP_DIR, 0, -4) . '/www/media/' . $page, 0755);
                $addressArr["pages_id"] = $page;

                $addressId = $this->database->table("contacts")->insert($addressArr);
            }
        } else {
            $addressId = $addressPreId;
        }

        $this->database->table("orders")->get($cartId)->update(array(
            "delivery_contacts_id" => $addressId,
        ));

    }

    function setInfo($email, $note = '', $phone = '')
    {
        $cart = $this->getCart();
        $this->database->table("orders")->get($cart->id)
            ->update(array(
                "note" => $note,
                "email" => $email,
                "phone" => $phone,
            ));
    }

    /**
     * Automatically synchronizes carts from registered and unregistered users
     */
    function removeBonus()
    {
        $cart = $this->getCart();
        $this->database->table("orders")->get($cart->id)
            ->update(array("store_bonus_id" => null));
    }

    /**
     * Remove carts left after 2 days
     */
    function cleanCarts()
    {
        $this->database->table("orders")->where(array(
            "date_created < ?" => date('Y-m-d', strtotime('-3 days', strtotime(date('Y-m-d')))),
            "orders_states_id" => null
        ))->delete();
    }

    /**
     * Can I buy the good or it is unavailable
     */
    function isAvailable($stockId)
    {
        $stockPrice = $this->database->table("stock")->where(array(
            "id" => $stockId,
        ));

        if ($stockPrice->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function updateContent()
    {
        if ($this->user) {
            $users_id = $this->user;
        } else {
            $users_id = 0;
        }

        $storeDb = $this->database->table("orders")->where(array(
            "uid" => session_id(),
            "orders_states_id" => null,
        ));

        if ($storeDb->count() == 0) {
            $storeId = $this->database->table("orders")->insert(array(
                "users_id" => $users_id,
                "orders_states_id" => null,
                "uid" => session_id(),
                "store_settings_shipping_id" => 5, // TODO: Get primary shipping method instead of guessing
                "store_settings_payments_id" => 7, // TODO: Get primary payment method instead of guessing
                "date_created" => date('Y-m-d H:i:s'),
            ));
        } else {
            $storeId = $storeDb->fetch()->id;

            $this->database->table("orders")->get($storeId)
                ->update(array(
                    "date_created" => date('Y-m-d H:i:s'),
                ));
        }
    }

    /**
     * Insert or update items
     */
    function updateItems($stockId, $amount)
    {
        $stock = $this->database->table("stock")->where(array(
            "id" => $stockId,
        ));

        if ($stock->count() > 0) {
            $stockPrice = $stock->fetch();
        } else {
            return false;
        }

        $cartItemExists = $this->database->table("orders_items")->where(array(
            "orders_id" => $this->getCartId(),
            "stock_id" => $stockId,
        ));

        $store = $this->database->table("store")->where(array("pages_id" => $stockPrice->pages_id))->fetch();

        if ($cartItemExists->count() == 0) {
            $this->database->table("orders_items")->insert(array(
                "orders_id" => $this->getCartId(),
                "pages_id" => $stockPrice->pages_id,
                "stock_id" => $stockId,
                "amount" => $amount,
                "price" => $store->price,
                "store_settings_vats_id" => $store->store_settings_vats_id,
                "date_created" => date('Y-m-d H:i:s'),
            ));
        } else {
            $cieId = $cartItemExists->fetch();
            $this->database->table("orders_items")->where(array(
                "id" => $cieId->id))->update(array(
                "amount" => $cieId->amount + $amount,
            ));
        }
    }

}
