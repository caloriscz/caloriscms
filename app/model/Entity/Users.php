<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", indexes={@ORM\Index(name="categories_id", columns={"users_categories_id"}), @ORM\Index(name="users_roles_id", columns={"users_roles_id"})})
 * @ORM\Entity
 */
class Users
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
     * @ORM\Column(name="username", type="string", length=40, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=80, nullable=false)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="sex", type="integer", nullable=false)
     */
    private $sex = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=80, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=60, nullable=false)
     */
    private $password;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_visited", type="datetime", nullable=true)
     */
    private $dateVisited;

    /**
     * @var integer
     *
     * @ORM\Column(name="state", type="integer", nullable=false)
     */
    private $state = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="activation", type="string", length=40, nullable=true)
     */
    private $activation;

    /**
     * @var integer
     *
     * @ORM\Column(name="newsletter", type="integer", nullable=true)
     */
    private $newsletter = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="login_error", type="integer", nullable=false)
     */
    private $loginError = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="login_success", type="integer", nullable=false)
     */
    private $loginSuccess = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="adminbar_enabled", type="smallint", nullable=false)
     */
    private $adminbarEnabled = '1';

    /**
     * @var \App\Model\Entity\UsersRoles
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\UsersRoles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_roles_id", referencedColumnName="id")
     * })
     */
    private $usersRoles;

    /**
     * @var \App\Model\Entity\UsersCategories
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\UsersCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="users_categories_id", referencedColumnName="id")
     * })
     */
    private $usersCategories;



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
     * Set username
     *
     * @param string $username
     *
     * @return Users
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Users
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
     * Set sex
     *
     * @param integer $sex
     *
     * @return Users
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return integer
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Users
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
     * Set password
     *
     * @param string $password
     *
     * @return Users
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Users
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
     * Set dateVisited
     *
     * @param \DateTime $dateVisited
     *
     * @return Users
     */
    public function setDateVisited($dateVisited)
    {
        $this->dateVisited = $dateVisited;

        return $this;
    }

    /**
     * Get dateVisited
     *
     * @return \DateTime
     */
    public function getDateVisited()
    {
        return $this->dateVisited;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return Users
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set activation
     *
     * @param string $activation
     *
     * @return Users
     */
    public function setActivation($activation)
    {
        $this->activation = $activation;

        return $this;
    }

    /**
     * Get activation
     *
     * @return string
     */
    public function getActivation()
    {
        return $this->activation;
    }

    /**
     * Set newsletter
     *
     * @param integer $newsletter
     *
     * @return Users
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return integer
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Users
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set loginError
     *
     * @param integer $loginError
     *
     * @return Users
     */
    public function setLoginError($loginError)
    {
        $this->loginError = $loginError;

        return $this;
    }

    /**
     * Get loginError
     *
     * @return integer
     */
    public function getLoginError()
    {
        return $this->loginError;
    }

    /**
     * Set loginSuccess
     *
     * @param integer $loginSuccess
     *
     * @return Users
     */
    public function setLoginSuccess($loginSuccess)
    {
        $this->loginSuccess = $loginSuccess;

        return $this;
    }

    /**
     * Get loginSuccess
     *
     * @return integer
     */
    public function getLoginSuccess()
    {
        return $this->loginSuccess;
    }

    /**
     * Set adminbarEnabled
     *
     * @param integer $adminbarEnabled
     *
     * @return Users
     */
    public function setAdminbarEnabled($adminbarEnabled)
    {
        $this->adminbarEnabled = $adminbarEnabled;

        return $this;
    }

    /**
     * Get adminbarEnabled
     *
     * @return integer
     */
    public function getAdminbarEnabled()
    {
        return $this->adminbarEnabled;
    }

    /**
     * Set usersRoles
     *
     * @param \App\Model\Entity\UsersRoles $usersRoles
     *
     * @return Users
     */
    public function setUsersRoles(\App\Model\Entity\UsersRoles $usersRoles = null)
    {
        $this->usersRoles = $usersRoles;

        return $this;
    }

    /**
     * Get usersRoles
     *
     * @return \App\Model\Entity\UsersRoles
     */
    public function getUsersRoles()
    {
        return $this->usersRoles;
    }

    /**
     * Set usersCategories
     *
     * @param \App\Model\Entity\UsersCategories $usersCategories
     *
     * @return Users
     */
    public function setUsersCategories(\App\Model\Entity\UsersCategories $usersCategories = null)
    {
        $this->usersCategories = $usersCategories;

        return $this;
    }

    /**
     * Get usersCategories
     *
     * @return \App\Model\Entity\UsersCategories
     */
    public function getUsersCategories()
    {
        return $this->usersCategories;
    }
}
