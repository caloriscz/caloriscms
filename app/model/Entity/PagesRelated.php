<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PagesRelated
 *
 * @ORM\Table(name="pages_related", indexes={@ORM\Index(name="store_id", columns={"pages_id", "related_pages_id"}), @ORM\Index(name="related_store_id", columns={"related_pages_id"}), @ORM\Index(name="blog_id", columns={"pages_id"}), @ORM\Index(name="related_blog_id", columns={"related_pages_id"})})
 * @ORM\Entity
 */
class PagesRelated
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
     * @ORM\Column(name="description", type="string", length=120, nullable=true)
     */
    private $description;

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
     * @var \App\Model\Entity\Pages
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Pages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="related_pages_id", referencedColumnName="id")
     * })
     */
    private $relatedPages;



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
     * Set description
     *
     * @param string $description
     *
     * @return PagesRelated
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
     * Set pages
     *
     * @param \App\Model\Entity\Pages $pages
     *
     * @return PagesRelated
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
     * Set relatedPages
     *
     * @param \App\Model\Entity\Pages $relatedPages
     *
     * @return PagesRelated
     */
    public function setRelatedPages(\App\Model\Entity\Pages $relatedPages = null)
    {
        $this->relatedPages = $relatedPages;

        return $this;
    }

    /**
     * Get relatedPages
     *
     * @return \App\Model\Entity\Pages
     */
    public function getRelatedPages()
    {
        return $this->relatedPages;
    }
}
