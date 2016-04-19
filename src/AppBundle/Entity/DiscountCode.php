<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DiscountCodeRepository")
 * @ORM\Table(name="discount_code")
 */
class DiscountCode {
    
    const STATUS_NEW = 1;
    const STATUS_ASSIGNED = 2;
    const STATUS_USED = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_EXPIRED = 5;
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $status;  
    
    /**
     * @ORM\Column(type="string", length=8)
     */
    protected $code;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;    
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $expiresAt;    
    /**
     * @ORM\Column(type="integer")
     */
    protected $value;  

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="discountCodes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\OneToOne(targetEntity="EquipmentBooking", mappedBy="discountCode")
     */    
    protected $equipmentBooking;
    /**
     * @ORM\OneToOne(targetEntity="TalentBooking", mappedBy="discountCode")
     */    
    protected $talentBooking;

    public function setId($id)
    {
        return $this;
    }

    
    public function getId()
    {
        return $this->id;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function getStatusStr() {
        switch ($this->status) {
            case self::STATUS_NEW: return "new";
            case self::STATUS_ASSIGNED: return "assigned";
            case self::STATUS_USED: return "used";
            case self::STATUS_CANCELLED: return "cancelled";
            case self::STATUS_EXPIRED: return "expired";
            default:
                throw new RuntimeException("DiscountCode status corrupt!");
        }
    }
    
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    
    public function getValue()
    {
        return $this->value;
    }
    
    const dicount_code_length = 8;
    
    public static function GenerateCode($chars){
        
        $maxRange = count($chars) - 1;
        $code = "";        
        while (strlen($code) < DiscountCode::dicount_code_length) {
            $i = mt_rand(0, $maxRange);
            $code .= $chars[$i];
        }
        return $code;
    }
    
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
    
    public function getCode()
    {
        return $this->code;
    }
  
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    
    public function getUser()
    {
        return $this->user;
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

  
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

  
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    public function __construct()
    {
        
    }

    /**
     * Set equipmentBooking
     *
     * @param \AppBundle\Entity\EquipmentBooking $equipmentBooking
     *
     * @return DiscountCode
     */
    public function setEquipmentBooking(\AppBundle\Entity\EquipmentBooking $equipmentBooking = null)
    {
        $this->equipmentBooking = $equipmentBooking;

        return $this;
    }

    /**
     * Get equipmentBooking
     *
     * @return \AppBundle\Entity\EquipmentBooking
     */
    public function getEquipmentBooking()
    {
        return $this->equipmentBooking;
    }

    /**
     * Set talentBooking
     *
     * @param \AppBundle\Entity\TalentBooking $talentBooking
     *
     * @return DiscountCode
     */
    public function setTalentBooking(\AppBundle\Entity\TalentBooking $talentBooking = null)
    {
        $this->talentBooking = $talentBooking;

        return $this;
    }

    /**
     * Get talentBooking
     *
     * @return \AppBundle\Entity\TalentBooking
     */
    public function getTalentBooking()
    {
        return $this->talentBooking;
    }
}
