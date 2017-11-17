<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PagesTemplates
 *
 * @ORM\Table(name="pages_templates", indexes={@ORM\Index(name="pages_types_id", columns={"pages_types_id"})})
 * @ORM\Entity
 */
class PagesTemplates
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
     * @ORM\Column(name="template", type="string", length=250, nullable=false)
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=80, nullable=true)
     */
    private $title;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set template
     *
     * @param string $template
     *
     * @return PagesTemplates
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return PagesTemplates
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
     * Set pagesTypes
     *
     * @param \App\Model\Entity\PagesTypes $pagesTypes
     *
     * @return PagesTemplates
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
}
