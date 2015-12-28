<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EquipmentFeatureRepository")
 * @ORM\Table(name="equipment_feature")
 */
class EquipmentFeature
{
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="Equipment", inversedBy="features")
     * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id")
     * @ORM\Id
     */
    protected $equipment;
    
    /**
     * @ORM\ManyToOne(targetEntity="Feature")
     * @ORM\JoinColumn(name="feature_id", referencedColumnName="id")
     * @ORM\Id
     */
    protected $feature;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EquipmentFeature
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
     * Set equipment
     *
     * @param \AppBundle\Entity\Equipment $equipment
     *
     * @return EquipmentFeature
     */
    public function setEquipment(\AppBundle\Entity\Equipment $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get equipment
     *
     * @return \AppBundle\Entity\Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * Set feature
     *
     * @param \AppBundle\Entity\Feature $feature
     *
     * @return EquipmentFeature
     */
    public function setFeature(\AppBundle\Entity\Feature $feature = null)
    {
        $this->feature = $feature;

        return $this;
    }

    /**
     * Get feature
     *
     * @return \AppBundle\Entity\Feature
     */
    public function getFeature()
    {
        return $this->feature;
    }
}
