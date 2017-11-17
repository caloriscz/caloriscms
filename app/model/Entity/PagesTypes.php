<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PagesTypes
 *
 * @ORM\Table(name="pages_types")
 * @ORM\Entity
 */
class PagesTypes
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
     * @ORM\Column(name="content_type", type="string", length=40, nullable=false)
     */
    private $contentType;

    /**
     * @var string
     *
     * @ORM\Column(name="presenter", type="string", length=40, nullable=false)
     */
    private $presenter;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=40, nullable=false)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="prefix", type="string", length=60, nullable=true)
     */
    private $prefix;

    /**
     * @var integer
     *
     * @ORM\Column(name="admin_enabled", type="smallint", nullable=false)
     */
    private $adminEnabled = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="admin_link", type="string", length=200, nullable=true)
     */
    private $adminLink;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=40, nullable=true)
     */
    private $icon;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enable_snippets", type="boolean", nullable=false)
     */
    private $enableSnippets = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="enable_images", type="boolean", nullable=false)
     */
    private $enableImages = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="enable_files", type="boolean", nullable=false)
     */
    private $enableFiles = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="enable_related", type="boolean", nullable=false)
     */
    private $enableRelated = '1';



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
     * Set contentType
     *
     * @param string $contentType
     *
     * @return PagesTypes
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set presenter
     *
     * @param string $presenter
     *
     * @return PagesTypes
     */
    public function setPresenter($presenter)
    {
        $this->presenter = $presenter;

        return $this;
    }

    /**
     * Get presenter
     *
     * @return string
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    /**
     * Set action
     *
     * @param string $action
     *
     * @return PagesTypes
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     *
     * @return PagesTypes
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set adminEnabled
     *
     * @param integer $adminEnabled
     *
     * @return PagesTypes
     */
    public function setAdminEnabled($adminEnabled)
    {
        $this->adminEnabled = $adminEnabled;

        return $this;
    }

    /**
     * Get adminEnabled
     *
     * @return integer
     */
    public function getAdminEnabled()
    {
        return $this->adminEnabled;
    }

    /**
     * Set adminLink
     *
     * @param string $adminLink
     *
     * @return PagesTypes
     */
    public function setAdminLink($adminLink)
    {
        $this->adminLink = $adminLink;

        return $this;
    }

    /**
     * Get adminLink
     *
     * @return string
     */
    public function getAdminLink()
    {
        return $this->adminLink;
    }

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return PagesTypes
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set enableSnippets
     *
     * @param boolean $enableSnippets
     *
     * @return PagesTypes
     */
    public function setEnableSnippets($enableSnippets)
    {
        $this->enableSnippets = $enableSnippets;

        return $this;
    }

    /**
     * Get enableSnippets
     *
     * @return boolean
     */
    public function getEnableSnippets()
    {
        return $this->enableSnippets;
    }

    /**
     * Set enableImages
     *
     * @param boolean $enableImages
     *
     * @return PagesTypes
     */
    public function setEnableImages($enableImages)
    {
        $this->enableImages = $enableImages;

        return $this;
    }

    /**
     * Get enableImages
     *
     * @return boolean
     */
    public function getEnableImages()
    {
        return $this->enableImages;
    }

    /**
     * Set enableFiles
     *
     * @param boolean $enableFiles
     *
     * @return PagesTypes
     */
    public function setEnableFiles($enableFiles)
    {
        $this->enableFiles = $enableFiles;

        return $this;
    }

    /**
     * Get enableFiles
     *
     * @return boolean
     */
    public function getEnableFiles()
    {
        return $this->enableFiles;
    }

    /**
     * Set enableRelated
     *
     * @param boolean $enableRelated
     *
     * @return PagesTypes
     */
    public function setEnableRelated($enableRelated)
    {
        $this->enableRelated = $enableRelated;

        return $this;
    }

    /**
     * Get enableRelated
     *
     * @return boolean
     */
    public function getEnableRelated()
    {
        return $this->enableRelated;
    }
}
