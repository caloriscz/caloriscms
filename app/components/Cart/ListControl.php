<?php

namespace Caloriscz\Cart;

use Nette\Application\UI\Control;

class ListControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Finish order request
     */
    function createComponentChangeAmountCartForm()
    {
        $form = new \Nette\Forms\FilterForm;
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';
        $form->addHidden("id");
        $form->addText("amount")
            ->setType("number")
            ->setAttribute("class", "form-control text-right")
            ->setAttribute("style", "width: 60px; display: inline;");

        $form->addSubmit("submitm", "dictionary.main.Change")
            ->setAttribute("class", "btn btn-cart-in btn-sm")
            ->setAttribute("style", "display: inline;");

        $form->onSuccess[] = $this->changeAmountCartFormSucceeded;
        return $form;
    }

    function changeAmountCartFormSucceeded(\Nette\Forms\FilterForm $form)
    {
        if ($form->values->amount == 0) {
            $this->database->table("orders_items")
                ->get($form->values->id)
                ->delete();
        } else {
            $this->database->table("orders_items")
                ->get($form->values->id)->update(array(
                    "amount" => $form->values->amount,
                ));
        }

        $this->presenter->redirect(':Front:Cart:default', array("id" => null));
    }

    /**
     * Delete cart item
     */
    function handleDelete($id)
    {
        $this->database->table("orders_items")
            ->get($id)
            ->delete();

        $this->presenter->redirect(":Front:Cart:default", array("id" => null));
    }

    public function render($type = 'classic')
    {
        $template = $this->template;
        $template->settings = $this->presenter->template->settings;

        if ($type == 'mini') {
            $template->setFile(__DIR__ . '/ListMiniControl.latte');
        } else {
            $template->setFile(__DIR__ . '/ListControl.latte');
        }

        if ($this->template->settings['store:enabled']) {
            $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user->getId(), $this->presenter->template->isLoggedIn);
            $template->cartId = $cart->getCartId();
            $template->cartItems = $cart->getCartItems();
            $template->cartTotal = $cart->getCartTotal($template->settings);

            $template->cart = $this->database->table("orders")->where(array("uid" => session_id(), "orders_states_id" => null));
            $template->cartItems = $this->database->table("orders_items")->where("orders_id", $template->cart->fetch()->id);
        }

        $template->render();
    }

}