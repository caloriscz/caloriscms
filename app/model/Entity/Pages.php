<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pages
 *
 * @ORM\Table(name="pages", indexes={@ORM\Index(name="users_id", columns={"users_id"}), @ORM\Index(name="pages_id", columns={"pages_id"}), @ORM\Index(name="content_type", columns={"pages_types_id"}), @ORM\Index(name="pages_ibfk_7", columns={"pages_templates_id"})})
 * @ORM\Entity
 */
class Pages
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
     * @ORM\Column(name="slug", type="string", length=250, nullable=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=250, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="document", type="text", length=65535, nullable=true)
     */
    private $document;

    /**
     * @var string
     *
     * @ORM\Column(name="preview", type="text", length=65535, nullable=true)
     */
    private $preview;

    /**
     * @var integer
     *
     * @ORM\Column(name="public", type="integer", nullable=true)
     */
    private $public = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="metadesc", type="string", length=200, nullable=true)
     */
    private $metadesc;

    /**
     * @var string
     *
     * @ORM\Column(name="metakeys", type="string", length=150, nullable=true)
     */
    private $metakeys;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    private $dateCreated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_published", type="datetime", nullable=true)
     */
    private $datePublished;

    /**
     * @var integer
     *
     * @ORM\Column(name="sorted", type="integer", nullable=false)
     */
    private $sorted = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="editable", type="integer", nullable=false)
     */
    private $editable = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="recommended", type="boolean", nullable=true)
     */
    private $recommended = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="sitemap", type="boolean", nullable=false)
     */
    private $sitemap = '1';

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
     * @var \App\Model\Entity\Pages
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Pages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pages_id", referencedColumnName="id")
     * })
     */
    private $pages;

    /**
     * @var \App\Model\Entity\PagesTypes
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\PagesTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pages_types_id", referencedColumnName="id")
     * })
     */
    private $pagesTypes;

    /**
     * @var \App\Model\Entity\PagesTemplates
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\PagesTemplates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pages_templates_id", referencedColumnName="id")
     * })
     */
    private $pagesTemplates;



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
     * Set slug
     *
     * @param string $slug
     *
     * @return Pages
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Pages
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
     * Set document
     *
     * @param string $document
     *
     * @return Pages
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set preview
     *
     * @param string $preview
     *
     * @return Pages
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Get preview
     *
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set public
     *
     * @param integer $public
     *
     * @return Pages
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return integer
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set metadesc
     *
     * @param string $metadesc
     *
     * @return Pages
     */
    public function setMetadesc($metadesc)
    {
        $this->metadesc = $metadesc;

        return $this;
    }

    /**
     * Get metadesc
     *
     * @return string
     */
    public function getMetadesc()
    {
        return $this->metadesc;
    }

    /**
     * Set metakeys
     *
     * @param string $metakeys
     *
     * @return Pages
     */
    public function setMetakeys($metakeys)
    {
        $this->metakeys = $metakeys;

        return $this;
    }

    /**
     * Get metakeys
     *
     * @return string
     */
    public function getMetakeys()
    {
        return $this->metakeys;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Pages
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
     * Set datePublished
     *
     * @param \DateTime $datePublished
     *
     * @return Pages
     */
    public function setDatePublished($datePublished)
    {
        $this->datePublished = $datePublished;

        return $this;
    }

    /**
     * Get datePublished
     *
     * @return \DateTime
     */
    public function getDatePublished()
    {
        return $this->datePublished;
    }

    /**
     * Set sorted
     *
     * @param integer $sorted
     *
     * @return Pages
     */
    public function setSorted($sorted)
    {
        $this->sorted = $sorted;

        return $this;
    }

    /**
     * Get sorted
     *
     * @return integer
     */
    public function getSorted()
    {
        return $this->sorted;
    }

    /**
     * Set editable
     *
     * @param integer $editable
     *
     * @return Pages
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;

        return $this;
    }

    /**
     * Get editable
     *
     * @return integer
     */
    public function getEditable()
    {
        return $this->editable;
    }

    /**
     * Set recommended
     *
     * @param boolean $recommended
     *
     * @return Pages
     */
    public function setRecommended($recommended)
    {
        $this->recommended = $recommended;

        return $this;
    }

    /**
     * Get recommended
     *
     * @return boolean
     */
    public function getRecommended()
    {
        return $this->recommended;
    }

    /**
     * Set sitemap
     *
     * @param boolean $sitemap
     *
     * @return Pages
     */
    public function setSitemap($sitemap)
    {
        $this->sitemap = $sitemap;

        return $this;
    }

    /**
     * Get sitemap
     *
     * @return boolean
     */
    public function getSitemap()
    {
        return $this->sitemap;
    }

    /**
     * Set users
     *
     * @param \App\Model\Entity\Users $users
     *
     * @return Pages
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
     * Set pages
     *
     * @param \App\Model\Entity\Pages $pages
     *
     * @return Pages
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
     * Set pagesTypes
     *
     * @param \App\Model\Entity\PagesTypes $pagesTypes
     *
     * @return Pages
     */
    public function setPagesTypes(\App\Model\Entity\PagesTypes $pagesTypes = null)
    {
        $this->pagesTypes = $pagesTypes;

        return $this;
    }

    /**
     * Get pagesTypes
     *
     * @return \App\Model\Entity\PagesTypes
     */
    public function getPagesTypes()
    {
        return $this->pagesTypes;
    }

    /**
     * Set pagesTemplates
     *
     * @param \App\Model\Entity\PagesTemplates $pagesTemplates
     *
     * @return Pages
     */
    public function setPagesTemplates(\App\Model\Entity\PagesTemplates $pagesTemplates = null)
    {
        $this->pagesTemplates = $pagesTemplates;

        return $this;
    }

    /**
     * Get pagesTemplates
     *
     * @return \App\Model\Entity\PagesTemplates
     */
    public function getPagesTemplates()
    {
        return $this->pagesTemplates;
    }
}
