<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="PromoCodeRepository")
 * @ORM\Table(name="promo_code")
 */
class PromoCode {

    const STATUS_NEW = 1;
    const STATUS_USED = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_CANCELLED = 4;
    
    const TYPE_AMOUNT = 1;
    const TYPE_PERCENT = 2;
    

    /**
     * @ORM\Column(type="integer")
     * @ORM\id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="integer")
     */
    protected $status = 1;
    /**
     * @ORM\Column(type="integer")
     */
    protected $type = 1;    
    /**
     * @ORM\Column(type="integer")
     */
    protected $value;
    /**
     * @ORM\Column(type="string")
     */
    protected $code;
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $expiresAt;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;        
    /**
     * @ORM\OneToOne(targetEntity="EquipmentBooking", mappedBy="promoCode")
     */    
    protected $equipmentBooking;
    /**
     * @ORM\OneToOne(targetEntity="TalentBooking", mappedBy="promoCode")
     */    
    protected $talentBooking;

    
    public function getStatusStr() {
        switch ($this->status) {
            case self::STATUS_NEW: return "new";
            case self::STATUS_USED: return "used";
            case self::STATUS_CANCELLED: return "cancelled";
            case self::STATUS_EXPIRED: return "expired";
            default:
                throw new RuntimeException("DiscountCode status corrupt!");
        }
    }
    

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
     * Set status
     *
     * @param integer $status
     *
     * @return PromoCode
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return PromoCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PromoCode
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @param \DateTime $modifiedAt
     *
     * @return PromoCode
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return PromoCode
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set value
     *
     * @param integer $value
     *
     * @return PromoCode
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set expiresAt
     *
     * @param \DateTime $expiresAt
     *
     * @return PromoCode
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Get expiresAt
     *
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Set equipmentBooking
     *
     * @param \AppBundle\Entity\EquipmentBooking $equipmentBooking
     *
     * @return PromoCode
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
     * @return PromoCode
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

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return PromoCode
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }
}
