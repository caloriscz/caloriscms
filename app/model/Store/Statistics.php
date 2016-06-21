<?php

/*
 * Order numbers - amounts and prices
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model\Store;

/**
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Statistics
{

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Order statistics by days - prices
     */
    function getData($type = 'd', $domain = 'price')
    {
        if ($type == 'y') {
            $period = '%Y';
            $nr = 30;
            $interval = "date_created >= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)";
        } elseif ($type == 'm') {
            $period = '%Y-%m';
            $nr = 10;
            $interval = "date_created >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
        } else {
            $period = '%Y-%m-%d';
            $nr = 12;
            $interval = "date_created >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        }

        if ($domain == 'amount') {
            $domain = 'amount';
        } else {
            $domain = 'price';
        }

        $ordersDays = $this->database->table("orders_items")
            ->select("orders.orders_states_id, orders.date_created, DATE_FORMAT(orders.date_created, ?) AS dc, SUM(" . $domain . ") AS result", $period)
            ->where("orders.orders_states_id NOT", null)
            ->group("dc")
            ->having($interval)
            ->order("dc")
            ->limit($nr);

        if ($ordersDays->count() > 0) {
            foreach ($ordersDays as $item) {
                $arr[$item->dc] = $item->result;
            }
        } else {
            $arr = false;
        }

        return $arr;
    }

    function convertKeysToString($arr)
    {
        if ($arr != false) {
            foreach ($arr as $key => $value) {
                $result .= "'" . $key . "', ";
            }
        }

        return $result;
    }

}
