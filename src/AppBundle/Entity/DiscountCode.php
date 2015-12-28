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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="discountCodes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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
    
    /**
     * @ORM\OneToOne(targetEntity="Inquiry", inversedBy="discountCode");
     * @ORM\JoinColumn(name="inquiry_id", referencedColumnName="id")
     */
    private $inquiry;

    
    public function getStatusStr() {
        switch ($this->status) {
            case self::STATUS_NEW: return "new";
            case self::STATUS_ASSIGNED: return "assigned";
            case self::STATUS_USED: return "used";
            case self::STATUS_CANCELLED: return "cancelled";
            default:
                throw new RuntimeException("DiscountCode status corrupt!");
        }
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
     * Set inquiry
     *
     * @param \AppBundle\Entity\Inquiry $inquiry
     *
     * @return DiscountCode
     */
    public function setInquiry(\AppBundle\Entity\Inquiry $inquiry = null)
    {
        $this->inquiry = $inquiry;

        return $this;
    }

    /**
     * Get inquiry
     *
     * @return \AppBundle\Entity\Inquiry
     */
    public function getInquiry()
    {
        return $this->inquiry;
    }
}
