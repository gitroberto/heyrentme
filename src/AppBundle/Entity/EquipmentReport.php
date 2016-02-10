<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="EquipmentReportRepository")
 * @ORM\Table(name="equipment_report")
 */
class EquipmentReport
{
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
     * @ORM\OneToOne(targetEntity="Equipment")
     * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id")
     */
    protected $equipment;

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
    
    public function __construct()
    {
        
    }

}
