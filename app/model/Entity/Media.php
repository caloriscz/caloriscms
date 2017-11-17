<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Media
 *
 * @ORM\Table(name="media", indexes={@ORM\Index(name="albums_id", columns={"pages_id"})})
 * @ORM\Entity
 */
class Media
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
     * @ORM\Column(name="name", type="string", length=140, nullable=false)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="file_type", type="boolean", nullable=false)
     */
    private $fileType = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="filesize", type="integer", nullable=false)
     */
    private $filesize = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=250, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;

    /**
     * @var boolean
     *
     * @ORM\Column(name="detail_view", type="boolean", nullable=false)
     */
    private $detailView = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="sorted", type="integer", nullable=false)
     */
    private $sorted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_file", type="boolean", nullable=false)
     */
    private $mainFile = '0';

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
     * Set name
     *
     * @param string $name
     *
     * @return Media
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
     * Set fileType
     *
     * @param boolean $fileType
     *
     * @return Media
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * Get fileType
     *
     * @return boolean
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * Set filesize
     *
     * @param integer $filesize
     *
     * @return Media
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * Get filesize
     *
     * @return integer
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Media
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
     * Set description
     *
     * @param string $description
     *
     * @return Media
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return Media
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
     * Set detailView
     *
     * @param boolean $detailView
     *
     * @return Media
     */
    public function setDetailView($detailView)
    {
        $this->detailView = $detailView;

        return $this;
    }

    /**
     * Get detailView
     *
     * @return boolean
     */
    public function getDetailView()
    {
        return $this->detailView;
    }

    /**
     * Set sorted
     *
     * @param integer $sorted
     *
     * @return Media
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
     * Set mainFile
     *
     * @param boolean $mainFile
     *
     * @return Media
     */
    public function setMainFile($mainFile)
    {
        $this->mainFile = $mainFile;

        return $this;
    }

    /**
     * Get mainFile
     *
     * @return boolean
     */
    public function getMainFile()
    {
        return $this->mainFile;
    }

    /**
     * Set pages
     *
     * @param \App\Model\Entity\Pages $pages
     *
     * @return Media
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
