<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HelpdeskMessages
 *
 * @ORM\Table(name="helpdesk_messages", indexes={@ORM\Index(name="helpdesk_id", columns={"helpdesk_id"})})
 * @ORM\Entity
 */
class HelpdeskMessages
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
     * @ORM\Column(name="message", type="text", length=65535, nullable=true)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=120, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="ipaddress", type="string", length=80, nullable=false)
     */
    private $ipaddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var \App\Model\Entity\Helpdesk
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Helpdesk")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="helpdesk_id", referencedColumnName="id")
     * })
     */
    private $helpdesk;



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
     * Set message
     *
     * @param string $message
     *
     * @return HelpdeskMessages
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return HelpdeskMessages
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set ipaddress
     *
     * @param string $ipaddress
     *
     * @return HelpdeskMessages
     */
    public function setIpaddress($ipaddress)
    {
        $this->ipaddress = $ipaddress;

        return $this;
    }

    /**
     * Get ipaddress
     *
     * @return string
     */
    public function getIpaddress()
    {
        return $this->ipaddress;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return HelpdeskMessages
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
     * Set helpdesk
     *
     * @param \App\Model\Entity\Helpdesk $helpdesk
     *
     * @return HelpdeskMessages
     */
    public function setHelpdesk(\App\Model\Entity\Helpdesk $helpdesk = null)
    {
        $this->helpdesk = $helpdesk;

        return $this;
    }

    /**
     * Get helpdesk
     *
     * @return \App\Model\Entity\Helpdesk
     */
    public function getHelpdesk()
    {
        return $this->helpdesk;
    }
}
