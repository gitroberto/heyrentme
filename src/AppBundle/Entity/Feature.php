<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\FeatureRepository")
 * @ORM\Table(name="feature")
 */
class Feature
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $name;
     /**
     * @ORM\Column(type="string", length=128)
     */
    protected $short_name;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $freetext;
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;
    /**
     * @ORM\ManyToOne(targetEntity="FeatureSection", inversedBy="features")
     * @ORM\JoinColumn(name="feature_section_id", referencedColumnName="id")
     */
    protected $featureSection;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    /**
     * Set name
     *
     * @param string $name
     *
     * @return Feature
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

    public function setShortName($shortname)
    {
        $this->short_name = $shortname;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->short_name;
    }
    
    /**
     * Set freetext
     *
     * @param integer $freetext
     *
     * @return Feature
     */
    public function setFreetext($freetext)
    {
        $this->freetext = $freetext;

        return $this;
    }

    /**
     * Get freetext
     *
     * @return integer
     */
    public function getFreetext()
    {
        return $this->freetext;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Feature
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set featureSection
     *
     * @param \AppBundle\Entity\FeatureSection $featureSection
     *
     * @return Feature
     */
    public function setFeatureSection(\AppBundle\Entity\FeatureSection $featureSection = null)
    {
        $this->featureSection = $featureSection;

        return $this;
    }

    /**
     * Get featureSection
     *
     * @return \AppBundle\Entity\FeatureSection
     */
    public function getFeatureSection()
    {
        return $this->featureSection;
    }
}
