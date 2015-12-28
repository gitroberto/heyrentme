<?php

namespace AppBundle\Entity;

use AppBundle\Utils\Utils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DiscountRepository")
 * @ORM\Table(name="discount")
 */
class Discount
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Equipment", inversedBy="discounts")
     * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id")
     */
    protected $equipment;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $type;
    /**
     * @ORM\Column(type="integer")
     */
    protected $percent;   
    /**
     * @ORM\Column(type="integer")
     */
    protected $duration;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $expiresAt;    
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    public function getPercent()
    {
        return $this->percent;
    }
    
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }
    
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
    
    public function setEquipment(Equipment $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment()
    {
        return $this->equipment;
    }
}
