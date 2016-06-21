<?php

use Nette\Application\UI\Control;

class ProductControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Instant add to cart +1 amount
     * @param type $id
     */
    function handleAddToCartInstant($id)
    {
        $name = $this->presenter->getName();

        $cart = new \App\Model\Store\Cart($this->database, $this->presenter->user->getId(), $this->presenter->template->isLoggedIn);
        $cart->updateContent();

        if ($cart->isAvailable($id)) {
            $cart->updateItems($id, 1);
        }

        if ($name == 'Front:Homepage') {
            $this->getPresenter()->redirect(':' . $name . ':default', array("id" => null));
        } else {
            $store = $this->database->table("stock")->get($id)->store->pages->slug;

            if (strlen($store) > 0) {
                $this->getPresenter()->redirectUrl('/' . $store);
            } else {
                $this->getPresenter()->redirect(':Front:Homepage:default', array("id" => null));
            }
        }
    }

    public function render($item)
    {
        $template = $this->template;
        $template->isLoggedIn = $this->presenter->template->isLoggedIn;
        $template->settings = $this->presenter->template->settings;
        $template->setFile(__DIR__ . '/ProductControl.latte');

        $template->item = $item;

        $template->render();
    }

}
