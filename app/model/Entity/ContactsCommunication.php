<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContactsCommunication
 *
 * @ORM\Table(name="contacts_communication", indexes={@ORM\Index(name="contacts_id", columns={"contacts_id"})})
 * @ORM\Entity
 */
class ContactsCommunication
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
     * @ORM\Column(name="contacts_id", type="integer", nullable=false)
     */
    private $contactsId;

    /**
     * @var string
     *
     * @ORM\Column(name="communication_type", type="string", length=80, nullable=false)
     */
    private $communicationType;

    /**
     * @var string
     *
     * @ORM\Column(name="communication_value", type="string", length=250, nullable=false)
     */
    private $communicationValue;



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
     * Set contactsId
     *
     * @param integer $contactsId
     *
     * @return ContactsCommunication
     */
    public function setContactsId($contactsId)
    {
        $this->contactsId = $contactsId;

        return $this;
    }

    /**
     * Get contactsId
     *
     * @return integer
     */
    public function getContactsId()
    {
        return $this->contactsId;
    }

    /**
     * Set communicationType
     *
     * @param string $communicationType
     *
     * @return ContactsCommunication
     */
    public function setCommunicationType($communicationType)
    {
        $this->communicationType = $communicationType;

        return $this;
    }

    /**
     * Get communicationType
     *
     * @return string
     */
    public function getCommunicationType()
    {
        return $this->communicationType;
    }

    /**
     * Set communicationValue
     *
     * @param string $communicationValue
     *
     * @return ContactsCommunication
     */
    public function setCommunicationValue($communicationValue)
    {
        $this->communicationValue = $communicationValue;

        return $this;
    }

    /**
     * Get communicationValue
     *
     * @return string
     */
    public function getCommunicationValue()
    {
        return $this->communicationValue;
    }
}
