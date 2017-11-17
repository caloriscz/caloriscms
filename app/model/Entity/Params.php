<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Params
 *
 * @ORM\Table(name="params", indexes={@ORM\Index(name="store_id", columns={"pages_id"}), @ORM\Index(name="store_param_id", columns={"param_id"})})
 * @ORM\Entity
 */
class Params
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
     * @ORM\Column(name="paramvalue", type="string", length=120, nullable=false)
     */
    private $paramvalue;

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
     * @var \App\Model\Entity\Param
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Param")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="param_id", referencedColumnName="id")
     * })
     */
    private $param;



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
     * Set paramvalue
     *
     * @param string $paramvalue
     *
     * @return Params
     */
    public function setParamvalue($paramvalue)
    {
        $this->paramvalue = $paramvalue;

        return $this;
    }

    /**
     * Get paramvalue
     *
     * @return string
     */
    public function getParamvalue()
    {
        return $this->paramvalue;
    }

    /**
     * Set pages
     *
     * @param \App\Model\Entity\Pages $pages
     *
     * @return Params
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
     * Set param
     *
     * @param \App\Model\Entity\Param $param
     *
     * @return Params
     */
    public function setParam(\App\Model\Entity\Param $param = null)
    {
        $this->param = $param;

        return $this;
    }

    /**
     * Get param
     *
     * @return \App\Model\Entity\Param
     */
    public function getParam()
    {
        return $this->param;
    }
}
