<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="equipment_booking")
 * @ORM\Entity(repositoryClass="EquipmentBookingRepository")
 */
class EquipmentBooking {
    
    const STATUS_BOOKED = 1;
    const STATUS_USER_CANCELLED = 2;
    const STATUS_PROVIDER_CANCELLED = 3;
    const STATUS_SUCCESS = 4;
    
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /** 
     * @ORM\Column(type="integer")
     */
    private $status;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $modifiedAt;
    /** 
     * @ORM\Column(type="integer")
     */
    private $price;
    /** 
     * @ORM\Column(type="decimal")
     */
    private $deposit;
    /** 
     * @ORM\Column(type="decimal")
     */
    private $totalPrice;

    
    /** 
     * @ORM\Column(type="datetime")
     */
    private $noticeRentUserAt;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $noticeRentProviderAt;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $noticeAllOkUserAt;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $noticeAllOkProviderAt;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $noticeReturnUserAt;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $noticeReturnProviderAt;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $noticeRateUserAt;
    /** 
     * @ORM\Column(type="datetime")
     */
    private $noticeRateProviderAt;
    /** 
     * @ORM\Column(type="string")
     */
    private $rateUserUuid;
    /** 
     * @ORM\Column(type="string")
     */
    private $rateEquipmentUuid;
    
    /**
     * @ORM\OneToOne(targetEntity="EquipmentInquiry", inversedBy="booking");
     * @ORM\JoinColumn(name="inquiry_id", referencedColumnName="id")
     */
    private $inquiry;
    
    /**
     * @ORM\OneToOne(targetEntity="DiscountCode")
     * @ORM\JoinColumn(name="discount_code_id", referencedColumnName="id")
     */    
    private $discountCode;
    /**
     * @ORM\OneToMany(targetEntity="EquipmentBookingCancel", mappedBy="booking")
     */    
    private $cancels;
    /**
     * @ORM\OneToOne(targetEntity="EquipmentRating", mappedBy="booking")
     */    
    private $rating;
    /**
     * @ORM\OneToOne(targetEntity="UserRating", mappedBy="booking")
     */    
    private $userRating;
    
    
    public function getStatusAsString() {
        switch ($this->status) {
            case EquipmentBooking::STATUS_BOOKED: return "offen";
            case EquipmentBooking::STATUS_PROVIDER_CANCELLED: return "zurückgetreten";
            case EquipmentBooking::STATUS_USER_CANCELLED: return "storniert";
            case EquipmentBooking::STATUS_SUCCESS: return "erfolg";
            default:
                throw new Exception("invalid booking status", "", null);
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
     * @return EquipmentBooking
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return EquipmentBooking
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
     * @return EquipmentBooking
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
     * Set inquiry
     *
     * @param \AppBundle\Entity\EquipmentInquiry $inquiry
     *
     * @return EquipmentBooking
     */
    public function setInquiry(\AppBundle\Entity\EquipmentInquiry $inquiry = null)
    {
        $this->inquiry = $inquiry;

        return $this;
    }

    /**
     * Get inquiry
     *
     * @return \AppBundle\Entity\EquipmentInquiry
     */
    public function getInquiry()
    {
        return $this->inquiry;
    }

    /**
     * Set discountCode
     *
     * @param \AppBundle\Entity\DiscountCode $discountCode
     *
     * @return EquipmentBooking
     */
    public function setDiscountCode(\AppBundle\Entity\DiscountCode $discountCode = null)
    {
        $this->discountCode = $discountCode;

        return $this;
    }

    /**
     * Get discountCode
     *
     * @return \AppBundle\Entity\DiscountCode
     */
    public function getDiscountCode()
    {
        return $this->discountCode;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return EquipmentBooking
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set deposit
     *
     * @param string $deposit
     *
     * @return EquipmentBooking
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;

        return $this;
    }

    /**
     * Get deposit
     *
     * @return string
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * Set totalPrice
     *
     * @param string $totalPrice
     *
     * @return EquipmentBooking
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set noticeRentUserAt
     *
     * @param \DateTime $noticeRentUserAt
     *
     * @return EquipmentBooking
     */
    public function setNoticeRentUserAt($noticeRentUserAt)
    {
        $this->noticeRentUserAt = $noticeRentUserAt;

        return $this;
    }

    /**
     * Get noticeRentUserAt
     *
     * @return \DateTime
     */
    public function getNoticeRentUserAt()
    {
        return $this->noticeRentUserAt;
    }

    /**
     * Set noticeRentProviderAt
     *
     * @param \DateTime $noticeRentProviderAt
     *
     * @return EquipmentBooking
     */
    public function setNoticeRentProviderAt($noticeRentProviderAt)
    {
        $this->noticeRentProviderAt = $noticeRentProviderAt;

        return $this;
    }

    /**
     * Get noticeRentProviderAt
     *
     * @return \DateTime
     */
    public function getNoticeRentProviderAt()
    {
        return $this->noticeRentProviderAt;
    }

    /**
     * Set noticeReturnUserAt
     *
     * @param \DateTime $noticeReturnUserAt
     *
     * @return EquipmentBooking
     */
    public function setNoticeReturnUserAt($noticeReturnUserAt)
    {
        $this->noticeReturnUserAt = $noticeReturnUserAt;

        return $this;
    }

    /**
     * Get noticeReturnUserAt
     *
     * @return \DateTime
     */
    public function getNoticeReturnUserAt()
    {
        return $this->noticeReturnUserAt;
    }

    /**
     * Set noticeReturnProviderAt
     *
     * @param \DateTime $noticeReturnProviderAt
     *
     * @return EquipmentBooking
     */
    public function setNoticeReturnProviderAt($noticeReturnProviderAt)
    {
        $this->noticeReturnProviderAt = $noticeReturnProviderAt;

        return $this;
    }

    /**
     * Get noticeReturnProviderAt
     *
     * @return \DateTime
     */
    public function getNoticeReturnProviderAt()
    {
        return $this->noticeReturnProviderAt;
    }

    /**
     * Set noticeRateUserAt
     *
     * @param \DateTime $noticeRateUserAt
     *
     * @return EquipmentBooking
     */
    public function setNoticeRateUserAt($noticeRateUserAt)
    {
        $this->noticeRateUserAt = $noticeRateUserAt;

        return $this;
    }

    /**
     * Get noticeRateUserAt
     *
     * @return \DateTime
     */
    public function getNoticeRateUserAt()
    {
        return $this->noticeRateUserAt;
    }

    /**
     * Set noticeRateProviderAt
     *
     * @param \DateTime $noticeRateProviderAt
     *
     * @return EquipmentBooking
     */
    public function setNoticeRateProviderAt($noticeRateProviderAt)
    {
        $this->noticeRateProviderAt = $noticeRateProviderAt;

        return $this;
    }

    /**
     * Get noticeRateProviderAt
     *
     * @return \DateTime
     */
    public function getNoticeRateProviderAt()
    {
        return $this->noticeRateProviderAt;
    }

    /**
     * Set noticeAllOkUserAt
     *
     * @param \DateTime $noticeAllOkUserAt
     *
     * @return EquipmentBooking
     */
    public function setNoticeAllOkUserAt($noticeAllOkUserAt)
    {
        $this->noticeAllOkUserAt = $noticeAllOkUserAt;

        return $this;
    }

    /**
     * Get noticeAllOkUserAt
     *
     * @return \DateTime
     */
    public function getNoticeAllOkUserAt()
    {
        return $this->noticeAllOkUserAt;
    }

    /**
     * Set noticeAllOkProviderAt
     *
     * @param \DateTime $noticeAllOkProviderAt
     *
     * @return EquipmentBooking
     */
    public function setNoticeAllOkProviderAt($noticeAllOkProviderAt)
    {
        $this->noticeAllOkProviderAt = $noticeAllOkProviderAt;

        return $this;
    }

    /**
     * Get noticeAllOkProviderAt
     *
     * @return \DateTime
     */
    public function getNoticeAllOkProviderAt()
    {
        return $this->noticeAllOkProviderAt;
    }

    /**
     * Set rateUserUuid
     *
     * @param string $rateUserUuid
     *
     * @return EquipmentBooking
     */
    public function setRateUserUuid($rateUserUuid)
    {
        $this->rateUserUuid = $rateUserUuid;

        return $this;
    }

    /**
     * Get rateUserUuid
     *
     * @return string
     */
    public function getRateUserUuid()
    {
        return $this->rateUserUuid;
    }

    /**
     * Set rateEquipmentUuid
     *
     * @param string $rateEquipmentUuid
     *
     * @return EquipmentBooking
     */
    public function setRateEquipmentUuid($rateEquipmentUuid)
    {
        $this->rateEquipmentUuid = $rateEquipmentUuid;

        return $this;
    }

    /**
     * Get rateEquipmentUuid
     *
     * @return string
     */
    public function getRateEquipmentUuid()
    {
        return $this->rateEquipmentUuid;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cancels = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add cancel
     *
     * @param \AppBundle\Entity\EquipmentBookingCancel $cancel
     *
     * @return EquipmentBooking
     */
    public function addCancel(\AppBundle\Entity\EquipmentBookingCancel $cancel)
    {
        $this->cancels[] = $cancel;

        return $this;
    }

    /**
     * Remove cancel
     *
     * @param \AppBundle\Entity\EquipmentBookingCancel $cancel
     */
    public function removeCancel(\AppBundle\Entity\EquipmentBookingCancel $cancel)
    {
        $this->cancels->removeElement($cancel);
    }

    /**
     * Get cancels
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCancels()
    {
        return $this->cancels;
    }

    /**
     * Set rating
     *
     * @param \AppBundle\Entity\EquipmentRating $rating
     *
     * @return EquipmentBooking
     */
    public function setRating(\AppBundle\Entity\EquipmentRating $rating = null)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return \AppBundle\Entity\EquipmentRating
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set userRating
     *
     * @param \AppBundle\Entity\UserRating $userRating
     *
     * @return EquipmentBooking
     */
    public function setUserRating(\AppBundle\Entity\UserRating $userRating = null)
    {
        $this->userRating = $userRating;

        return $this;
    }

    /**
     * Get userRating
     *
     * @return \AppBundle\Entity\UserRating
     */
    public function getUserRating()
    {
        return $this->userRating;
    }
}
