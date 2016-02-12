<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ReportOfferRepository")
 * @ORM\Table(name="report_offer")
 */
class ReportOffer
{
    const OFFER_TYPE_EQUIPMENT = 1;
    const OFFER_TYPE_TALENT = 2;
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $report;  
    
    /**
     * @ORM\Column(type="string", length=500)
     */
    protected $message;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;   
    
    /**
     * @ORM\ManyToOne(targetEntity="Equipment")
     * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id")
     
     */
    protected $equipment;
    
    /**
     * @ORM\ManyToOne(targetEntity="Talent")
     * @ORM\JoinColumn(name="talent_id", referencedColumnName="id")
     
     */
    protected $talent;
    
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $offerType;

    public function setId($id)
    {
        return $this;
    }

    
    public function getId()
    {
        return $this->id;
    }

    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    public function getReport()
    {
        return $this->report;
    }
    
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
    
    public function getMessage()
    {
        return $this->message;
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
    
    public function setEquipment($equipment)
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment()
    {
        return $this->equipment;
    }
    
    public function setTalent($talent)
    {
        $this->talent = $talent;

        return $this;
    }

    public function getTalent()
    {
        return $this->talent;
    }
    
    public function setOfferType($offerType)
    {
        $this->offerType = $offerType;

        return $this;
    }

    public function getOfferType()
    {
        return $this->offerType;
    }
    
    public function __construct()
    {
        
    }

}
