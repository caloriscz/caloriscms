<?php

/*
 * Product information
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model\Store;

/**
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Blog
{

    /** @var Nette\Database\Context */
    public $database;
    public $user;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Set product ID
     */
    function setId($id)
    {
        $this->id = $id;

        return $this->id;
    }

    /**
     * Get product ID
     */
    function getId()
    {
        $prodId = $this->database->table("pages")->get($this->id);

        if ($prodId) {
            return $prodId;
        } else {
            return FALSE;
        }
    }

}
