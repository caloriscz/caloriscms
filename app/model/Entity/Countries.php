<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Countries
 *
 * @ORM\Table(name="countries")
 * @ORM\Entity
 */
class Countries
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
     * @ORM\Column(name="title_cs", type="string", length=120, nullable=true)
     */
    private $titleCs;

    /**
     * @var string
     *
     * @ORM\Column(name="title_en", type="string", length=120, nullable=true)
     */
    private $titleEn;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show", type="boolean", nullable=false)
     */
    private $show;



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
     * Set titleCs
     *
     * @param string $titleCs
     *
     * @return Countries
     */
    public function setTitleCs($titleCs)
    {
        $this->titleCs = $titleCs;

        return $this;
    }

    /**
     * Get titleCs
     *
     * @return string
     */
    public function getTitleCs()
    {
        return $this->titleCs;
    }

    /**
     * Set titleEn
     *
     * @param string $titleEn
     *
     * @return Countries
     */
    public function setTitleEn($titleEn)
    {
        $this->titleEn = $titleEn;

        return $this;
    }

    /**
     * Get titleEn
     *
     * @return string
     */
    public function getTitleEn()
    {
        return $this->titleEn;
    }

    /**
     * Set show
     *
     * @param boolean $show
     *
     * @return Countries
     */
    public function setShow($show)
    {
        $this->show = $show;

        return $this;
    }

    /**
     * Get show
     *
     * @return boolean
     */
    public function getShow()
    {
        return $this->show;
    }
}
