<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ReportOffertRepository")
 * @ORM\Table(name="report_offert")
 */
class ReportOffert
{
    const OFFERT_TYPE_EQUIPMENT = 1;
    const OFFERT_TYPE_TALENT = 2;
    
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
     * @ORM\Column(type="integer")
     */
    protected $offertId;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $offertType;

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
    
    public function setOffertId($offertId)
    {
        $this->offertId = $offertId;

        return $this;
    }

    public function getOffertId()
    {
        return $this->offertId;
    }
    
    public function setOffertType($offertType)
    {
        $this->offertType = $offertType;

        return $this;
    }

    public function getOffertType()
    {
        return $this->offertType;
    }
    
    public function __construct()
    {
        
    }

}
