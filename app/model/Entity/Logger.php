<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Logger
 *
 * @ORM\Table(name="logger")
 * @ORM\Entity
 */
class Logger
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=200, nullable=false)
     */
    private $event;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="users_id", type="integer", nullable=true)
     */
    private $usersId;

    /**
     * @var integer
     *
     * @ORM\Column(name="pages_id", type="integer", nullable=true)
     */
    private $pagesId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var integer
     *
     * @ORM\Column(name="event_types_id", type="integer", nullable=false)
     */
    private $eventTypesId = '0';



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set event
     *
     * @param string $event
     *
     * @return Logger
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Logger
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set usersId
     *
     * @param integer $usersId
     *
     * @return Logger
     */
    public function setUsersId($usersId)
    {
        $this->usersId = $usersId;

        return $this;
    }

    /**
     * Get usersId
     *
     * @return integer
     */
    public function getUsersId()
    {
        return $this->usersId;
    }

    /**
     * Set pagesId
     *
     * @param integer $pagesId
     *
     * @return Logger
     */
    public function setPagesId($pagesId)
    {
        $this->pagesId = $pagesId;

        return $this;
    }

    /**
     * Get pagesId
     *
     * @return integer
     */
    public function getPagesId()
    {
        return $this->pagesId;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Logger
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set eventTypesId
     *
     * @param integer $eventTypesId
     *
     * @return Logger
     */
    public function setEventTypesId($eventTypesId)
    {
        $this->eventTypesId = $eventTypesId;

        return $this;
    }

    /**
     * Get eventTypesId
     *
     * @return integer
     */
    public function getEventTypesId()
    {
        return $this->eventTypesId;
    }
}
