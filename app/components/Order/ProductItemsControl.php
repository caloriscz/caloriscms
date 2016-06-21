<?php

use Nette\Application\UI\Control;

class ProductItemsControl extends Control
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function render($order = null)
    {
        $template = $this->template;

        $template->items = $this->presenter->template->cartItemsArr;
        $template->order = $order;
        $template->shippingByWeight = $this->database->table("store_settings_weights");
        $template->weights = $this->database->table("store_settings_weights");

        $template->settings = $this->presenter->template->settings;

        $template->setFile(__DIR__ . '/ProductItemsControl.latte');

        $template->render();
    }

}
