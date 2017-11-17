<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Events
 *
 * @ORM\Table(name="events", indexes={@ORM\Index(name="pages_id", columns={"pages_id"}), @ORM\Index(name="contacts_id", columns={"contacts_id"})})
 * @ORM\Entity
 */
class Events
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_event", type="datetime", nullable=true)
     */
    private $dateEvent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_event_end", type="datetime", nullable=true)
     */
    private $dateEventEnd;

    /**
     * @var boolean
     *
     * @ORM\Column(name="all_day", type="boolean", nullable=false)
     */
    private $allDay = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="show", type="boolean", nullable=false)
     */
    private $show = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="capacity", type="integer", nullable=false)
     */
    private $capacity = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="capacity_start", type="integer", nullable=false)
     */
    private $capacityStart = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="capacity_filled", type="integer", nullable=false)
     */
    private $capacityFilled = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="time_range", type="integer", nullable=true)
     */
    private $timeRange = '0';

    /**
     * @var \App\Model\Entity\Pages
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Pages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pages_id", referencedColumnName="id")
     * })
     */
    private $pages;

    /**
     * @var \App\Model\Entity\Contacts
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Contacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contacts_id", referencedColumnName="id")
     * })
     */
    private $contacts;



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
     * Set dateEvent
     *
     * @param \DateTime $dateEvent
     *
     * @return Events
     */
    public function setDateEvent($dateEvent)
    {
        $this->dateEvent = $dateEvent;

        return $this;
    }

    /**
     * Get dateEvent
     *
     * @return \DateTime
     */
    public function getDateEvent()
    {
        return $this->dateEvent;
    }

    /**
     * Set dateEventEnd
     *
     * @param \DateTime $dateEventEnd
     *
     * @return Events
     */
    public function setDateEventEnd($dateEventEnd)
    {
        $this->dateEventEnd = $dateEventEnd;

        return $this;
    }

    /**
     * Get dateEventEnd
     *
     * @return \DateTime
     */
    public function getDateEventEnd()
    {
        return $this->dateEventEnd;
    }

    /**
     * Set allDay
     *
     * @param boolean $allDay
     *
     * @return Events
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;

        return $this;
    }

    /**
     * Get allDay
     *
     * @return boolean
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * Set show
     *
     * @param boolean $show
     *
     * @return Events
     */
    public function setShow($show)
    {
        $this->show = $show;

        return $this;
    }

    /**
     * Get show
     *
     * @return boolean
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * Set capacity
     *
     * @param integer $capacity
     *
     * @return Events
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity
     *
     * @return integer
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set capacityStart
     *
     * @param integer $capacityStart
     *
     * @return Events
     */
    public function setCapacityStart($capacityStart)
    {
        $this->capacityStart = $capacityStart;

        return $this;
    }

    /**
     * Get capacityStart
     *
     * @return integer
     */
    public function getCapacityStart()
    {
        return $this->capacityStart;
    }

    /**
     * Set capacityFilled
     *
     * @param integer $capacityFilled
     *
     * @return Events
     */
    public function setCapacityFilled($capacityFilled)
    {
        $this->capacityFilled = $capacityFilled;

        return $this;
    }

    /**
     * Get capacityFilled
     *
     * @return integer
     */
    public function getCapacityFilled()
    {
        return $this->capacityFilled;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Events
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set timeRange
     *
     * @param integer $timeRange
     *
     * @return Events
     */
    public function setTimeRange($timeRange)
    {
        $this->timeRange = $timeRange;

        return $this;
    }

    /**
     * Get timeRange
     *
     * @return integer
     */
    public function getTimeRange()
    {
        return $this->timeRange;
    }

    /**
     * Set pages
     *
     * @param \App\Model\Entity\Pages $pages
     *
     * @return Events
     */
    public function setPages(\App\Model\Entity\Pages $pages = null)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * Get pages
     *
     * @return \App\Model\Entity\Pages
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set contacts
     *
     * @param \App\Model\Entity\Contacts $contacts
     *
     * @return Events
     */
    public function setContacts(\App\Model\Entity\Contacts $contacts = null)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Get contacts
     *
     * @return \App\Model\Entity\Contacts
     */
    public function getContacts()
    {
        return $this->contacts;
    }
}
