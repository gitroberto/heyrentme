<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EquipmentImage {
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */    
    protected $image;
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Equipment", inversedBy="images")
     * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id")
     */
    protected $equipment;
    /**
     * @ORM\Column(type="integer")
     */
    protected $main;

    /**
     * Set main
     *
     * @param integer $main
     *
     * @return EquipmentImage
     */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
     * Get main
     *
     * @return integer
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return EquipmentImage
     */
    public function setImage(\AppBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \AppBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set equipment
     *
     * @param \AppBundle\Entity\Equipemnt $equipment
     *
     * @return EquipmentImage
     */
    public function setEquipment(\AppBundle\Entity\Equipment $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get equipment
     *
     * @return \AppBundle\Entity\Equipemnt
     */
    public function getEquipment()
    {
        return $this->equipment;
    }
}
