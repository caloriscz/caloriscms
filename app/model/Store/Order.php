<?php

/*
 * Order
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model\Store;

/**
 * Cart model
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Order
{

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Get order table by Id
     */
    function getTableById($id)
    {
        $orderDb = $this->database->table("orders")->get($id);

        if ($orderDb) {
            return $orderDb;
        } else {
            return FALSE;
        }
    }

    /**
     * Get order table
     */
    function getTable()
    {
        $orderDb = $this->database->table("orders");

        if ($orderDb->count() > 0) {
            return $orderDb;
        } else {
            return FALSE;
        }
    }

}
