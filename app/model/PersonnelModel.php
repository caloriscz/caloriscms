<?php

/*
 * Personál a seznamy
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * Personál
 * @author Petr Karásek <caloris@caloris.cz>
 */
class PersonnelModel
{

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function getList()
    {
        $contacts = $this->database->table('contacts')
                        ->where(array("contacts_groups_id" => 1))
                        ->order('name')->fetchPairs('email', 'name');
        
        return $contacts;
    }

}
