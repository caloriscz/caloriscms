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
class Bonus
{

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Set user
     */
    function setUser($id)
    {
        $this->user = $id;

        return $this->user;
    }

    /**
     * Set cart amount
     */
    function setCartTotal($sum)
    {
        $this->total = $sum;

        return $this->total;
    }

    /**
     * Get list of bonuses
     */
    function getBonuses()
    {
        $bonusesDb = $this->database->table("store_bonus")->where(array(
            "from <= ?" => $this->total,
        ));

        if ($bonusesDb->count() > 0) {
            return $bonusesDb;
        } else {
            return false;
        }
    }

    /**
     * Get list of bonuses
     */
    function getAmount($cartTotal = 0)
    {
        $bonusesDb = $this->database->table("store_bonus")->order("from")->limit(1);

        if ($bonusesDb->count() > 0) {
            $amount = $cartTotal - $bonusesDb->fetch()->from;
            return $amount;
        } else {
            return false;
        }
    }

    /**
     * Are you eligible for any bonus - check for settings and sum
     */
    function isEligible($bonusId)
    {
        $bonusesDb = $this->database->table("store_bonus")->get($bonusId);

        if ($bonusesDb && $bonusesDb->from < $this->total) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Are you eligible for this bonus
     */
    function isEligibleForBonus($group)
    {
        if (!in_array($group, array(82, 83, 84))) {
            return false;
        }

        $bonusesDb = $this->database->table("store_bonus")->order("from")->limit(1);

        if ($this->total < $bonusesDb->fetch()->from) {
            return false;
        } else {
            return true;
        }
    }

}
