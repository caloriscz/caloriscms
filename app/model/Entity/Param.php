<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Param
 *
 * @ORM\Table(name="param")
 * @ORM\Entity
 */
class Param
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
     * @ORM\Column(name="param", type="string", length=80, nullable=false)
     */
    private $param;

    /**
     * @var string
     *
     * @ORM\Column(name="param_en", type="string", length=80, nullable=false)
     */
    private $paramEn;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="prefix", type="string", length=40, nullable=true)
     */
    private $prefix;

    /**
     * @var string
     *
     * @ORM\Column(name="suffix", type="string", length=40, nullable=true)
     */
    private $suffix;

    /**
     * @var string
     *
     * @ORM\Column(name="preset", type="string", length=80, nullable=true)
     */
    private $preset;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ignore_front", type="boolean", nullable=false)
     */
    private $ignoreFront = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="ignore_admin", type="boolean", nullable=false)
     */
    private $ignoreAdmin = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type_front", type="string", length=40, nullable=false)
     */
    private $typeFront = 'radio';

    /**
     * @var integer
     *
     * @ORM\Column(name="sorted", type="integer", nullable=true)
     */
    private $sorted = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="block_class", type="string", length=60, nullable=true)
     */
    private $blockClass = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="replace_param", type="string", length=250, nullable=true)
     */
    private $replaceParam;



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
     * Set param
     *
     * @param string $param
     *
     * @return Param
     */
    public function setParam($param)
    {
        $this->param = $param;

        return $this;
    }

    /**
     * Get param
     *
     * @return string
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Set paramEn
     *
     * @param string $paramEn
     *
     * @return Param
     */
    public function setParamEn($paramEn)
    {
        $this->paramEn = $paramEn;

        return $this;
    }

    /**
     * Get paramEn
     *
     * @return string
     */
    public function getParamEn()
    {
        return $this->paramEn;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Param
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
     * Set prefix
     *
     * @param string $prefix
     *
     * @return Param
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
     * Set suffix
     *
     * @param string $suffix
     *
     * @return Param
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Get suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Set preset
     *
     * @param string $preset
     *
     * @return Param
     */
    public function setPreset($preset)
    {
        $this->preset = $preset;

        return $this;
    }

    /**
     * Get preset
     *
     * @return string
     */
    public function getPreset()
    {
        return $this->preset;
    }

    /**
     * Set ignoreFront
     *
     * @param boolean $ignoreFront
     *
     * @return Param
     */
    public function setIgnoreFront($ignoreFront)
    {
        $this->ignoreFront = $ignoreFront;

        return $this;
    }

    /**
     * Get ignoreFront
     *
     * @return boolean
     */
    public function getIgnoreFront()
    {
        return $this->ignoreFront;
    }

    /**
     * Set ignoreAdmin
     *
     * @param boolean $ignoreAdmin
     *
     * @return Param
     */
    public function setIgnoreAdmin($ignoreAdmin)
    {
        $this->ignoreAdmin = $ignoreAdmin;

        return $this;
    }

    /**
     * Get ignoreAdmin
     *
     * @return boolean
     */
    public function getIgnoreAdmin()
    {
        return $this->ignoreAdmin;
    }

    /**
     * Set typeFront
     *
     * @param string $typeFront
     *
     * @return Param
     */
    public function setTypeFront($typeFront)
    {
        $this->typeFront = $typeFront;

        return $this;
    }

    /**
     * Get typeFront
     *
     * @return string
     */
    public function getTypeFront()
    {
        return $this->typeFront;
    }

    /**
     * Set sorted
     *
     * @param integer $sorted
     *
     * @return Param
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
     * Set blockClass
     *
     * @param string $blockClass
     *
     * @return Param
     */
    public function setBlockClass($blockClass)
    {
        $this->blockClass = $blockClass;

        return $this;
    }

    /**
     * Get blockClass
     *
     * @return string
     */
    public function getBlockClass()
    {
        return $this->blockClass;
    }

    /**
     * Set replaceParam
     *
     * @param string $replaceParam
     *
     * @return Param
     */
    public function setReplaceParam($replaceParam)
    {
        $this->replaceParam = $replaceParam;

        return $this;
    }

    /**
     * Get replaceParam
     *
     * @return string
     */
    public function getReplaceParam()
    {
        return $this->replaceParam;
    }
}
