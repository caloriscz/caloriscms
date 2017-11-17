<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContactsOpeninghours
 *
 * @ORM\Table(name="contacts_openinghours", indexes={@ORM\Index(name="contacts_id", columns={"contacts_id"})})
 * @ORM\Entity
 */
class ContactsOpeninghours
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
     * @var integer
     *
     * @ORM\Column(name="day", type="smallint", nullable=false)
     */
    private $day;

    /**
     * @var string
     *
     * @ORM\Column(name="hourstext", type="string", length=80, nullable=true)
     */
    private $hourstext;

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
     * Set day
     *
     * @param integer $day
     *
     * @return ContactsOpeninghours
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return integer
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set hourstext
     *
     * @param string $hourstext
     *
     * @return ContactsOpeninghours
     */
    public function setHourstext($hourstext)
    {
        $this->hourstext = $hourstext;

        return $this;
    }

    /**
     * Get hourstext
     *
     * @return string
     */
    public function getHourstext()
    {
        return $this->hourstext;
    }

    /**
     * Set contacts
     *
     * @param \App\Model\Entity\Contacts $contacts
     *
     * @return ContactsOpeninghours
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
