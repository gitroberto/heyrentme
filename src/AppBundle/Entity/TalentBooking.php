<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="talent_booking")
 * @ORM\Entity(repositoryClass="TalentBookingRepository")
 */
class TalentBooking {
    
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
    private $rateTalentUuid;
    
    /**
     * @ORM\OneToOne(targetEntity="TalentInquiry", inversedBy="booking");
     * @ORM\JoinColumn(name="talent_inquiry_id", referencedColumnName="id")
     */
    private $inquiry;
    
    /**
     * @ORM\OneToOne(targetEntity="DiscountCode")
     * @ORM\JoinColumn(name="discount_code_id", referencedColumnName="id")
     */    
    private $discountCode;
    /**
     * @ORM\OneToOne(targetEntity="PromoCode")
     * @ORM\JoinColumn(name="promo_code_id", referencedColumnName="id")
     */    
    private $promoCode;
    /**
     * @ORM\OneToMany(targetEntity="TalentBookingCancel", mappedBy="talentBooking")
     */    
    private $cancels;
    /**
     * @ORM\OneToOne(targetEntity="TalentRating", mappedBy="booking")
     */    
    private $rating;
    /**
     * @ORM\OneToOne(targetEntity="UserRating", mappedBy="talentBooking")
     */    
    private $userRating;    
    
    public function getStatusAsString() {
        switch ($this->status) {
            case TalentBooking::STATUS_BOOKED: return "offen";
            case TalentBooking::STATUS_PROVIDER_CANCELLED: return "zurückgetreten";
            case TalentBooking::STATUS_USER_CANCELLED: return "storniert";
            case TalentBooking::STATUS_SUCCESS: return "erfolg";
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
     * @return TalentBooking
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
     * @return TalentBooking
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
     * @return TalentBooking
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
     * Set price
     *
     * @param integer $price
     *
     * @return TalentBooking
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set totalPrice
     *
     * @param string $totalPrice
     *
     * @return TalentBooking
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
     * @return TalentBooking
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
     * @return TalentBooking
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
     * Set noticeAllOkUserAt
     *
     * @param \DateTime $noticeAllOkUserAt
     *
     * @return TalentBooking
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
     * @return TalentBooking
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
     * Set noticeReturnUserAt
     *
     * @param \DateTime $noticeReturnUserAt
     *
     * @return TalentBooking
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
     * @return TalentBooking
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
     * @return TalentBooking
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
     * @return TalentBooking
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
     * Set rateUserUuid
     *
     * @param string $rateUserUuid
     *
     * @return TalentBooking
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
     * Set rateTalentUuid
     *
     * @param string $rateTalentUuid
     *
     * @return TalentBooking
     */
    public function setRateTalentUuid($rateTalentUuid)
    {
        $this->rateTalentUuid = $rateTalentUuid;

        return $this;
    }

    /**
     * Get rateTalentUuid
     *
     * @return string
     */
    public function getRateTalentUuid()
    {
        return $this->rateTalentUuid;
    }

    /**
     * Set inquiry
     *
     * @param \AppBundle\Entity\TalentInquiry $inquiry
     *
     * @return TalentBooking
     */
    public function setInquiry(\AppBundle\Entity\TalentInquiry $inquiry = null)
    {
        $this->inquiry = $inquiry;

        return $this;
    }

    /**
     * Get inquiry
     *
     * @return \AppBundle\Entity\TalentInquiry
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
     * @return TalentBooking
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
     * Constructor
     */
    public function __construct()
    {
        $this->cancels = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add cancel
     *
     * @param \AppBundle\Entity\TalentBookingCancel $cancel
     *
     * @return TalentBooking
     */
    public function addCancel(\AppBundle\Entity\TalentBookingCancel $cancel)
    {
        $this->cancels[] = $cancel;

        return $this;
    }

    /**
     * Remove cancel
     *
     * @param \AppBundle\Entity\TalentBookingCancel $cancel
     */
    public function removeCancel(\AppBundle\Entity\TalentBookingCancel $cancel)
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
     * @param \AppBundle\Entity\TalentRating $rating
     *
     * @return TalentBooking
     */
    public function setRating(\AppBundle\Entity\TalentRating $rating = null)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return \AppBundle\Entity\TalentRating
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
     * @return TalentBooking
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

    /**
     * Set promoCode
     *
     * @param \AppBundle\Entity\PromoCode $promoCode
     *
     * @return TalentBooking
     */
    public function setPromoCode(\AppBundle\Entity\PromoCode $promoCode = null)
    {
        $this->promoCode = $promoCode;

        return $this;
    }

    /**
     * Get promoCode
     *
     * @return \AppBundle\Entity\PromoCode
     */
    public function getPromoCode()
    {
        return $this->promoCode;
    }
}
