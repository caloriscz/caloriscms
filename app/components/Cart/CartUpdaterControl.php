<?php

namespace Caloriscz\Cart;

use Nette\Application\UI\Control;

class CartUpdaterControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function createComponentAddToCartForm()
    {
        $form = new \Nette\Forms\BootstrapPHForm();
        $form->setTranslator($this->presenter->translator);
        $form->getElementPrototype()->class = "form-horizontal";
        $form->getElementPrototype()->role = 'form';

        $form->addHidden("id");
        $form->addHidden("stock");
        $form->addHidden("slug");
        $form->addText("amount")
            ->setType("number")
            ->setAttribute("style", "max-width: 60px; font-size: 1.05em;")
            ->setAttribute("class", "form-control text-right");
        $form->setDefaults(array("slug" => $this->getParameter("id")));

        $form->addSubmit("submitm", "store.cart.add")
            ->setAttribute("class", "btn btn-cart btn-sm");

        $form->onSuccess[] = $this->addToCartFormSucceeded;
        return $form;
    }

    function addToCartFormSucceeded(\Nette\Forms\BootstrapPHForm $form)
    {
        $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user->getId(), $this->presenter->template->isLoggedIn);
        $cart->updateContent();

        if ($cart->isAvailable($form->values->stock)) {
            $cart->updateItems($form->values->stock, $form->values->amount);
        }

        $this->presenter->redirectUrl('/' . $form->values->slug);
    }

    public function render($page)
    {
        $template = $this->template;
        $template->database = $this->database;
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/CartUpdaterControl.latte');
        $template->page = $page;

        $template->render();
    }

}
