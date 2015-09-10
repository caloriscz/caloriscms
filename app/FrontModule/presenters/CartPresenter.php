<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

/**
 * Cart presenter.
 */
class CartPresenter extends BasePresenter
{

    /**
     * Finish order request
     */
    function createComponentChangeAmountCartForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm;
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->class = "form-inline";
        $form->getElementPrototype()->role = 'form';
        $form->getElementPrototype()->autocomplete = 'off';

        $form->addHidden("id");
        $form->addText("amount")
                ->setType("number")
                ->setAttribute("class", "form-control text-right")
                ->setAttribute("style", "width: 60px;");

        $form->addSubmit("submitm", "Change")
                ->setAttribute("class", "btn btn-success btn-sm");

        $form->onSuccess[] = $this->changeAmountCartFormSucceeded;
        return $form;
    }

    function changeAmountCartFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        if ($form->values->amount == 0) {
            $this->database->table("cart_items")
                    ->get($form->values->id)
                    ->delete();
        } else {
            $this->database->table("cart_items")
                    ->get($form->values->id)->update(array(
                "amount" => $form->values->amount,
            ));
        }

        $this->redirect(':Front:Cart:default');
    }

    /**
     * Delete post
     */
    function handleDelete($id)
    {
        $this->database->table("cart_items")
                ->get($id)
                ->delete();

        $this->redirect(":Front:Cart:default");
    }

    function renderDefault()
    {
        if ($this->user->isLoggedIn()) {
            $this->template->cart = $this->database->table("cart_items")
                    ->where(array("users_id" => $this->user->getId()))
                    ->order("id");
        } else {
            $cart = $this->database->table("cart")->where(array("uid" => $_COOKIE["PHPSESSID"]));

            $this->template->cart = $this->database->table("cart_items")
                    ->where(array("cart_id" => $cart->fetch()->id))
                    ->order("id");
        }
    }

}
