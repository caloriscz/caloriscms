<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contacts
 *
 * @ORM\Table(name="contacts", indexes={@ORM\Index(name="users_id", columns={"users_id"}), @ORM\Index(name="categories_id", columns={"contacts_categories_id"}), @ORM\Index(name="countries_id", columns={"countries_id"}), @ORM\Index(name="pages_id", columns={"pages_id"})})
 * @ORM\Entity
 */
class Contacts
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
     * @var boolean
     *
     * @ORM\Column(name="type", type="boolean", nullable=false)
     */
    private $type = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="post", type="string", length=80, nullable=true)
     */
    private $post;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=65535, nullable=true)
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=120, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=120, nullable=true)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=120, nullable=true)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=80, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=10, nullable=true)
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=80, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=150, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="vatin", type="string", length=10, nullable=true)
     */
    private $vatin;

    /**
     * @var string
     *
     * @ORM\Column(name="vatid", type="string", length=10, nullable=true)
     */
    private $vatid;

    /**
     * @var string
     *
     * @ORM\Column(name="banking_account", type="string", length=80, nullable=true)
     */
    private $bankingAccount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=false)
     */
    private $order;

    /**
     * @var \App\Model\Entity\Users
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_id", referencedColumnName="id")
     * })
     */
    private $users;

    /**
     * @var \App\Model\Entity\Countries
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Countries")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="countries_id", referencedColumnName="id")
     * })
     */
    private $countries;

    /**
     * @var \App\Model\Entity\ContactsCategories
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\ContactsCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contacts_categories_id", referencedColumnName="id")
     * })
     */
    private $contactsCategories;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param boolean $type
     *
     * @return Contacts
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return boolean
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set post
     *
     * @param string $post
     *
     * @return Contacts
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return string
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Contacts
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Contacts
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set company
     *
     * @param string $company
     *
     * @return Contacts
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Contacts
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Contacts
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Contacts
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Contacts
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
     * Set phone
     *
     * @param string $phone
     *
     * @return Contacts
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set vatin
     *
     * @param string $vatin
     *
     * @return Contacts
     */
    public function setVatin($vatin)
    {
        $this->vatin = $vatin;

        return $this;
    }

    /**
     * Get vatin
     *
     * @return string
     */
    public function getVatin()
    {
        return $this->vatin;
    }

    /**
     * Set vatid
     *
     * @param string $vatid
     *
     * @return Contacts
     */
    public function setVatid($vatid)
    {
        $this->vatid = $vatid;

        return $this;
    }

    /**
     * Get vatid
     *
     * @return string
     */
    public function getVatid()
    {
        return $this->vatid;
    }

    /**
     * Set bankingAccount
     *
     * @param string $bankingAccount
     *
     * @return Contacts
     */
    public function setBankingAccount($bankingAccount)
    {
        $this->bankingAccount = $bankingAccount;

        return $this;
    }

    /**
     * Get bankingAccount
     *
     * @return string
     */
    public function getBankingAccount()
    {
        return $this->bankingAccount;
    }

    /**
     * Set dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     *
     * @return Contacts
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * Get dateOfBirth
     *
     * @return \DateTime
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Contacts
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set users
     *
     * @param \App\Model\Entity\Users $users
     *
     * @return Contacts
     */
    public function setUsers(\App\Model\Entity\Users $users = null)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get users
     *
     * @return \App\Model\Entity\Users
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set countries
     *
     * @param \App\Model\Entity\Countries $countries
     *
     * @return Contacts
     */
    public function setCountries(\App\Model\Entity\Countries $countries = null)
    {
        $this->countries = $countries;

        return $this;
    }

    /**
     * Get countries
     *
     * @return \App\Model\Entity\Countries
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Set contactsCategories
     *
     * @param \App\Model\Entity\ContactsCategories $contactsCategories
     *
     * @return Contacts
     */
    public function setContactsCategories(\App\Model\Entity\ContactsCategories $contactsCategories = null)
    {
        $this->contactsCategories = $contactsCategories;

        return $this;
    }

    /**
     * Get contactsCategories
     *
     * @return \App\Model\Entity\ContactsCategories
     */
    public function getContactsCategories()
    {
        return $this->contactsCategories;
    }

    /**
     * Set pages
     *
     * @param \App\Model\Entity\Pages $pages
     *
     * @return Contacts
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
}
