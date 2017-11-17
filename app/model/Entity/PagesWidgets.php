<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PagesWidgets
 *
 * @ORM\Table(name="pages_widgets", indexes={@ORM\Index(name="pages_id", columns={"pages_id"})})
 * @ORM\Entity
 */
class PagesWidgets
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
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="sorted", type="integer", nullable=false)
     */
    private $sorted;

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
     * Set title
     *
     * @param string $title
     *
     * @return PagesWidgets
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
     * @return PagesWidgets
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
     * Set sorted
     *
     * @param integer $sorted
     *
     * @return PagesWidgets
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
     * Set pages
     *
     * @param \App\Model\Entity\Pages $pages
     *
     * @return PagesWidgets
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
