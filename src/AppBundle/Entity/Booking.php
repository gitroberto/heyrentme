<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**

 * @ORM\Table(name="booking")
 * @ORM\Entity
 */
class Booking {
    
    const STATUS_BOOKED = 1;
    
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
    private $rating;
    /** 
     * @ORM\Column(type="decimal")
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
     * @ORM\OneToOne(targetEntity="Inquiry", inversedBy="booking");
     * @ORM\JoinColumn(name="inquiry_id", referencedColumnName="id")
     */
    private $inquiry;
    
    /**
     * @ORM\OneToOne(targetEntity="DiscountCode")
     * @ORM\JoinColumn(name="discount_code_id", referencedColumnName="id")
     */    
    private $discountCode;

    
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
     * @return Booking
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
     * @return Booking
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
     * @return Booking
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
     * Set rating
     *
     * @param integer $rating
     *
     * @return Booking
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set inquiry
     *
     * @param \AppBundle\Entity\Inquiry $inquiry
     *
     * @return Booking
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

    /**
     * Set discountCode
     *
     * @param \AppBundle\Entity\DiscountCode $discountCode
     *
     * @return Booking
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
     * @return Booking
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
     * @return Booking
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
     * @return Booking
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
}
