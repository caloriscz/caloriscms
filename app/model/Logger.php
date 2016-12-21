<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Logger
{

    /** @var \Nette\Database\Context */
    private $database;

    public function __construct(\Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function setEvent($event)
    {
        $this->event = $event;
    }

    function getEvent()
    {
        return $this->event;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function getDescription()
    {
        return $this->description;
    }

    function setUser($user)
    {
        $this->user = $user;
    }

    function getUser()
    {
        return $this->user;
    }

    function setType($type)
    {
        $this->type = $type;
    }

    function getType()
    {
        if (!isset($this->type)) {
            $this->type = 0;
        }

        return $this->type;
    }

    function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    function getPageId()
    {
        return $this->pageId;
    }


    /**
     * Save event to database
     */
    function save()
    {
       /* echo "<br>";
        echo "<strong>event:</strong> " . $this->getEvent() . "<br>";
        echo "<strong>description:</strong> " . $this->getDescription() . "<br>";
        echo "<strong>user:</strong> " . $this->getUser() . "<br>";
        echo "<strong>type:</strong> " . $this->getType() . "<br>";*/

        $arr = array(
            "event" => $this->getEvent(),
            "description" => $this->getDescription(),
            "users_id" => $this->getUser(),
            "date_created" => date("Y-m-d H:i:s"),
            "event_types_id" => $this->getType(),
        );

        if ($this->getPageId()) {
            $arr["pages_id"] = $this->getPageId();
        }

        $this->database->table("logger")->insert($arr);


        return true;
    }

}
