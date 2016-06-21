<?php

/*
 * Bonus information - is user eligible
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model\Store;

/**
 * @author Petr Karásek <caloris@caloris.cz>
 */
class DeliveryCalaculator
{

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function setSettings($settings)
    {
        $this->settings = $settings;
        return $this->settings;
    }

    function getSettings()
    {
        return $this->settings;
    }

    function setOrder($order)
    {
        $this->order = $order;
        return $this->order;
    }

    function getOrder()
    {
        return $this->order;
    }

    function getWeights($shippingId, $weight)
    {
        return $this->database->table("store_settings_weights")->where('store_settings_shipping_id = ? AND upto >= ?', $shippingId, $weight)->order('upto ASC');
    }

    /**
     * Calculate shipping price
     */
    function calculateShipping()
    {
        $weights = $this->getWeights($this->order->store_settings_shipping->id, $this->order->related('orders_items')->sum('stock.weight * orders_items.amount'));
        $settings = $this->getSettings();

        if ($this->order->related('orders_items')->sum('price * amount') <= $this->order->store_settings_shipping->free_from) {
            if ($settings['store:stock:shippingByWeight']) {
                if ($weights->count() > 0) {
                    $ship = $weights->fetch()->price;
                } else {
                    $ship = $this->order->store_settings_shipping->shipping;
                }
            } else {
                $ship = $this->order->store_settings_shipping->shipping;
            }
        } else {
            $ship = 0;
        }

        return $ship;
    }

    function calculatePayment()
    {
        if ($this->order->related('orders_items')->sum('price * amount') <= $this->order->store_settings_payments->free_from) {
            $payment = $this->order->store_settings_payments->payment;
        } else {
            $payment = 0;
        }

        return $payment;
    }

    function calculateWeight()
    {
        return $this->order->related('orders_items')->sum('stock.weight * orders_items.amount');
    }

}
