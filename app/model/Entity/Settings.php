<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table(name="settings", indexes={@ORM\Index(name="categories_id", columns={"settings_categories_id"})})
 * @ORM\Entity
 */
class Settings
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
     * @ORM\Column(name="setkey", type="string", length=40, nullable=false)
     */
    private $setkey;

    /**
     * @var string
     *
     * @ORM\Column(name="setvalue", type="string", length=120, nullable=false)
     */
    private $setvalue;

    /**
     * @var string
     *
     * @ORM\Column(name="description_cs", type="string", length=150, nullable=true)
     */
    private $descriptionCs;

    /**
     * @var string
     *
     * @ORM\Column(name="description_en", type="string", length=150, nullable=true)
     */
    private $descriptionEn;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=40, nullable=true)
     */
    private $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="admin_editable", type="boolean", nullable=false)
     */
    private $adminEditable = '0';

    /**
     * @var \App\Model\Entity\SettingsCategories
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\SettingsCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="settings_categories_id", referencedColumnName="id")
     * })
     */
    private $settingsCategories;



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
     * Set setkey
     *
     * @param string $setkey
     *
     * @return Settings
     */
    public function setSetkey($setkey)
    {
        $this->setkey = $setkey;

        return $this;
    }

    /**
     * Get setkey
     *
     * @return string
     */
    public function getSetkey()
    {
        return $this->setkey;
    }

    /**
     * Set setvalue
     *
     * @param string $setvalue
     *
     * @return Settings
     */
    public function setSetvalue($setvalue)
    {
        $this->setvalue = $setvalue;

        return $this;
    }

    /**
     * Get setvalue
     *
     * @return string
     */
    public function getSetvalue()
    {
        return $this->setvalue;
    }

    /**
     * Set descriptionCs
     *
     * @param string $descriptionCs
     *
     * @return Settings
     */
    public function setDescriptionCs($descriptionCs)
    {
        $this->descriptionCs = $descriptionCs;

        return $this;
    }

    /**
     * Get descriptionCs
     *
     * @return string
     */
    public function getDescriptionCs()
    {
        return $this->descriptionCs;
    }

    /**
     * Set descriptionEn
     *
     * @param string $descriptionEn
     *
     * @return Settings
     */
    public function setDescriptionEn($descriptionEn)
    {
        $this->descriptionEn = $descriptionEn;

        return $this;
    }

    /**
     * Get descriptionEn
     *
     * @return string
     */
    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Settings
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set adminEditable
     *
     * @param boolean $adminEditable
     *
     * @return Settings
     */
    public function setAdminEditable($adminEditable)
    {
        $this->adminEditable = $adminEditable;

        return $this;
    }

    /**
     * Get adminEditable
     *
     * @return boolean
     */
    public function getAdminEditable()
    {
        return $this->adminEditable;
    }

    /**
     * Set settingsCategories
     *
     * @param \App\Model\Entity\SettingsCategories $settingsCategories
     *
     * @return Settings
     */
    public function setSettingsCategories(\App\Model\Entity\SettingsCategories $settingsCategories = null)
    {
        $this->settingsCategories = $settingsCategories;

        return $this;
    }

    /**
     * Get settingsCategories
     *
     * @return \App\Model\Entity\SettingsCategories
     */
    public function getSettingsCategories()
    {
        return $this->settingsCategories;
    }
}
