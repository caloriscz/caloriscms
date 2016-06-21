<?php

namespace Caloriscz\Cart;

use Nette\Application\UI\Control;

class BoxControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render()
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/BoxControl.latte');

        if ($this->template->settings['store:enabled']) {
            $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user->getId(), $this->presenter->template->isLoggedIn);
            $template->cartId = $cart->getCartId();
            $template->cartItems = $cart->getCartItems();
            $cartSum = $this->presenter->template->cartItems;

            if (is_numeric($cartSum)) {
                $template->cartSum = $cartSum;
            } else {
                $template->cartSum = 0;
            }

            $template->cartTotal = $cart->getCartTotal($this->template->settings);

            $template->cart = $this->database->table("orders")->where("uid", session_id());
            $template->cartItems = $this->database->table("orders_items")->where("orders_id", $template->cart->fetch()->id);
        }

        $template->render();
    }

}