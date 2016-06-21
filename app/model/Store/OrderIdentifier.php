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
class OrderIdentifier
{

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function setId($orderId, $type = 'numeric')
    {
        $orderDb = $this->database->table("orders_ids")->where("orders_id", $orderId);

        if ($orderDb->count() == 0) {
            if ($type == 'year') {
                return $this->getYearId($orderId);
            } else {

                return $this->getNumericId($orderId);
            }

            $this->identifier = $identifier;
        } else {
            return $this->identifier;
        }
    }

    /**
     * Get order id
     */
    function getId()
    {
        return $this->identifier;
    }

    /*
     * Check if orders with date identifier starts with 1 or continues
     */
    function getNumericId($transactId)
    {
        $orderLastIdDb = $this->database->table("orders_ids")->order("identifier_nr DESC")->limit(1);

        if ($orderLastIdDb->count() > 0) {
            $orderLastId = $orderLastIdDb->fetch();
        } else {
            $orderLastId = 1;
        }

        $identifier_nr = $orderLastId->identifier_nr + 1;

        $this->database->table("orders_ids")->insert(array(
            "identifier" => "DEM" . \Nette\Utils\Strings::padLeft($identifier_nr, 6, '0'),
            "orders_id" => $transactId,
            "identifier_nr" => $identifier_nr,
            "date_created" => date("Y-m-d H:i:s")
        ));

        $this->identifier = \Nette\Utils\Strings::padLeft($identifier_nr, 6, '0');

        return $this->identifier;
    }

    /*
     * Check if orders with date identifier starts with 1 or continues
     */
    function getYearId($transactId)
    {
        $orderLastIdDb = $this->database->table("orders_ids")->where("date_created >= YEAR()")->order("identifier_nr DESC")->limit(1);

        if ($orderLastIdDb->count() > 0) {
            $orderLastId = $orderLastIdDb->fetch()->identifier_nr + 1;
        } else {
            $orderLastId = 1;
        }

        $identifier_nr = $orderLastId->identifier_nr + 1;

        $this->database->table("orders_ids")->insert(array(
            "identifier" => "DEM" . date('Y') . \Nette\Utils\Strings::padLeft($identifier_nr, 4, '0'),
            "orders_id" => $transactId,
            "identifier_nr" => $identifier_nr,
            "date_created" => date("Y-m-d H:i:s")
        ));

        $this->identifier = "DEM" . date('Y') . \Nette\Utils\Strings::padLeft($identifier_nr, 4, '0');
    }

}
