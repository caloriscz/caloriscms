<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Nette\Database\Context;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Logger
{

    /** @var \Nette\Database\Context */
    private $database;

    private $pageId;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function setEvent($event)
    {
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        if (!isset($this->type)) {
            $this->type = 0;
        }

        return $this->type;
    }

    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    public function getPageId()
    {
        return $this->pageId;
    }


    /**
     * Save event to database
     */
    public function save()
    {
        $arr = [
            'event' => $this->getEvent(),
            'description' => $this->getDescription(),
            'users_id' => $this->getUser(),
            'date_created' => date('Y-m-d H:i:s'),
            'event_types_id' => $this->getType(),
        ];

        if ($this->getPageId()) {
            $arr['pages_id'] = $this->getPageId();
        }

        $this->database->table('logger')->insert($arr);


        return true;
    }

}
