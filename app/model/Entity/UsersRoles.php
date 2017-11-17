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
     * @ORM\Column(name="admin_access", type="boolean", nullable=false)
     */
    private $adminAccess = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="appearance_images", type="boolean", nullable=false)
     */
    private $appearanceImages = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="helpdesk_edit", type="boolean", nullable=false)
     */
    private $helpdeskEdit = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="settings_display", type="boolean", nullable=false)
     */
    private $settingsDisplay = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="settings_edit", type="boolean", nullable=false)
     */
    private $settingsEdit = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="members_display", type="boolean", nullable=false)
     */
    private $membersDisplay = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="members_edit", type="boolean", nullable=false)
     */
    private $membersEdit = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="members_create", type="boolean", nullable=false)
     */
    private $membersCreate = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="members_delete", type="boolean", nullable=false)
     */
    private $membersDelete = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="pages_edit", type="boolean", nullable=false)
     */
    private $pagesEdit = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="pages_document", type="boolean", nullable=false)
     */
    private $pagesDocument = '0';



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
     * Set adminAccess
     *
     * @param boolean $adminAccess
     *
     * @return UsersRoles
     */
    public function setAdminAccess($adminAccess)
    {
        $this->adminAccess = $adminAccess;

        return $this;
    }

    /**
     * Get adminAccess
     *
     * @return boolean
     */
    public function getAdminAccess()
    {
        return $this->adminAccess;
    }

    /**
     * Set appearanceImages
     *
     * @param boolean $appearanceImages
     *
     * @return UsersRoles
     */
    public function setAppearanceImages($appearanceImages)
    {
        $this->appearanceImages = $appearanceImages;

        return $this;
    }

    /**
     * Get appearanceImages
     *
     * @return boolean
     */
    public function getAppearanceImages()
    {
        return $this->appearanceImages;
    }

    /**
     * Set helpdeskEdit
     *
     * @param boolean $helpdeskEdit
     *
     * @return UsersRoles
     */
    public function setHelpdeskEdit($helpdeskEdit)
    {
        $this->helpdeskEdit = $helpdeskEdit;

        return $this;
    }

    /**
     * Get helpdeskEdit
     *
     * @return boolean
     */
    public function getHelpdeskEdit()
    {
        return $this->helpdeskEdit;
    }

    /**
     * Set settingsDisplay
     *
     * @param boolean $settingsDisplay
     *
     * @return UsersRoles
     */
    public function setSettingsDisplay($settingsDisplay)
    {
        $this->settingsDisplay = $settingsDisplay;

        return $this;
    }

    /**
     * Get settingsDisplay
     *
     * @return boolean
     */
    public function getSettingsDisplay()
    {
        return $this->settingsDisplay;
    }

    /**
     * Set settingsEdit
     *
     * @param boolean $settingsEdit
     *
     * @return UsersRoles
     */
    public function setSettingsEdit($settingsEdit)
    {
        $this->settingsEdit = $settingsEdit;

        return $this;
    }

    /**
     * Get settingsEdit
     *
     * @return boolean
     */
    public function getSettingsEdit()
    {
        return $this->settingsEdit;
    }

    /**
     * Set membersDisplay
     *
     * @param boolean $membersDisplay
     *
     * @return UsersRoles
     */
    public function setMembersDisplay($membersDisplay)
    {
        $this->membersDisplay = $membersDisplay;

        return $this;
    }

    /**
     * Get membersDisplay
     *
     * @return boolean
     */
    public function getMembersDisplay()
    {
        return $this->membersDisplay;
    }

    /**
     * Set membersEdit
     *
     * @param boolean $membersEdit
     *
     * @return UsersRoles
     */
    public function setMembersEdit($membersEdit)
    {
        $this->membersEdit = $membersEdit;

        return $this;
    }

    /**
     * Get membersEdit
     *
     * @return boolean
     */
    public function getMembersEdit()
    {
        return $this->membersEdit;
    }

    /**
     * Set membersCreate
     *
     * @param boolean $membersCreate
     *
     * @return UsersRoles
     */
    public function setMembersCreate($membersCreate)
    {
        $this->membersCreate = $membersCreate;

        return $this;
    }

    /**
     * Get membersCreate
     *
     * @return boolean
     */
    public function getMembersCreate()
    {
        return $this->membersCreate;
    }

    /**
     * Set membersDelete
     *
     * @param boolean $membersDelete
     *
     * @return UsersRoles
     */
    public function setMembersDelete($membersDelete)
    {
        $this->membersDelete = $membersDelete;

        return $this;
    }

    /**
     * Get membersDelete
     *
     * @return boolean
     */
    public function getMembersDelete()
    {
        return $this->membersDelete;
    }

    /**
     * Set pagesEdit
     *
     * @param boolean $pagesEdit
     *
     * @return UsersRoles
     */
    public function setPagesEdit($pagesEdit)
    {
        $this->pagesEdit = $pagesEdit;

        return $this;
    }

    /**
     * Get pagesEdit
     *
     * @return boolean
     */
    public function getPagesEdit()
    {
        return $this->pagesEdit;
    }

    /**
     * Set pagesDocument
     *
     * @param boolean $pagesDocument
     *
     * @return UsersRoles
     */
    public function setPagesDocument($pagesDocument)
    {
        $this->pagesDocument = $pagesDocument;

        return $this;
    }

    /**
     * Get pagesDocument
     *
     * @return boolean
     */
    public function getPagesDocument()
    {
        return $this->pagesDocument;
    }
}
