<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersRoles
 *
 * @ORM\Table(name="users_roles")
 * @ORM\Entity
 */
class UsersRoles
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
     * @ORM\Column(name="title", type="string", length=40, nullable=false)
     */
    private $title;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sign", type="boolean", nullable=false)
     */
    private $sign = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="appearance", type="boolean", nullable=false)
     */
    private $appearance = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="helpdesk", type="boolean", nullable=false)
     */
    private $helpdesk = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="settings", type="boolean", nullable=false)
     */
    private $settings = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="members", type="boolean", nullable=false)
     */
    private $members = '0';


    /**
     * @var boolean
     *
     * @ORM\Column(name="pages_edit", type="boolean", nullable=false)
     */
    private $pages = '0';


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
     * Set title
     *
     * @param string $title
     *
     * @return UsersRoles
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set sign
     *
     * @param boolean $sign
     *
     * @return UsersRoles
     */
    public function setSign($sign)
    {
        $this->sign = $sign;

        return $this;
    }

    /**
     * Get sign
     *
     * @return boolean
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * Set appearanceImages
     *
     * @param boolean $appearance
     *
     * @return UsersRoles
     */
    public function setAppearance($appearance)
    {
        $this->appearance = $appearance;

        return $this;
    }

    /**
     * Get appearance
     *
     * @return boolean
     */
    public function getAppearance()
    {
        return $this->appearance;
    }

    /**
     * Set helpdesk
     *
     * @param boolean $helpdesk
     *
     * @return UsersRoles
     */
    public function setHelpdesk($helpdesk)
    {
        $this->helpdesk = $helpdesk;

        return $this;
    }

    /**
     * Get helpdesk
     *
     * @return boolean
     */
    public function getHelpdesk()
    {
        return $this->helpdesk;
    }

    /**
     * Set settings
     *
     * @param boolean $settings
     *
     * @return UsersRoles
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settingsDisplay
     *
     * @return boolean
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set members
     *
     * @param boolean $members
     *
     * @return UsersRoles
     */
    public function setMembers($members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * Get membersDisplay
     *
     * @return boolean
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Set pagesEdit
     *
     * @param boolean $pages
     *
     * @return UsersRoles
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * Get pages
     *
     * @return boolean
     */
    public function getPages()
    {
        return $this->pages;
    }
}
