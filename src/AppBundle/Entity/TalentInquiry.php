<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TalentInquiryRepository")
 * @ORM\Table(name="talent_inquiry")
 */
class TalentInquiry {
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;
    /**
     * @ORM\Column(type="string", length=128)
     */
    private $email;
    /**
     * @ORM\Column(type="string")
     */
    private $message;
    /**
     * @ORM\Column(type="datetime")
     */
    private $fromAt;
    /**
     * @ORM\Column(type="datetime")
     */
    private $toAt;
    /**
     * @ORM\Column(type="decimal")
     */
    private $price;
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $accepted;
    /**
     * @ORM\Column(type="string")
     */
    private $response;
    /**
     * @ORM\Column(type="datetime")
     */
    private $modifiedAt;
    /**
     * @ORM\Column(type="string")
     */
    private $uuid;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="inquiries")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Talent")
     * @ORM\JoinColumn(name="talent_id", referencedColumnName="id")
     */
    private $talent;
    
    /**
     * @ORM\OneToOne(targetEntity="TalentBooking", mappedBy="inquiry")
     */
    private $booking;    

    
        
    public function getDescAsStr() {
        $from = $this->fromAt->format('Y-m-d H:i');
        $to = $this->toAt->format('Y-m-d H:i');
        return sprintf("from: %s\nto: %s\nprice: %.2f\nmessage: %s", $from, $to, $this->getPrice(), $this->getMessage());
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
     * Set name
     *
     * @param string $name
     *
     * @return TalentInquiry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return TalentInquiry
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return TalentInquiry
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set fromAt
     *
     * @param \DateTime $fromAt
     *
     * @return TalentInquiry
     */
    public function setFromAt($fromAt)
    {
        $this->fromAt = $fromAt;

        return $this;
    }

    /**
     * Get fromAt
     *
     * @return \DateTime
     */
    public function getFromAt()
    {
        return $this->fromAt;
    }

    /**
     * Set toAt
     *
     * @param \DateTime $toAt
     *
     * @return TalentInquiry
     */
    public function setToAt($toAt)
    {
        $this->toAt = $toAt;

        return $this;
    }

    /**
     * Get toAt
     *
     * @return \DateTime
     */
    public function getToAt()
    {
        return $this->toAt;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return TalentInquiry
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
     * @return TalentInquiry
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
     * Set priceBuy
     *
     * @param string $priceBuy
     *
     * @return TalentInquiry
     */
    public function setPriceBuy($priceBuy)
    {
        $this->priceBuy = $priceBuy;

        return $this;
    }

    /**
     * Get priceBuy
     *
     * @return string
     */
    public function getPriceBuy()
    {
        return $this->priceBuy;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TalentInquiry
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
     * Set accepted
     *
     * @param integer $accepted
     *
     * @return TalentInquiry
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return integer
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set response
     *
     * @param string $response
     *
     * @return TalentInquiry
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set modifiedAt
     *
     * @param \DateTime $modifiedAt
     *
     * @return TalentInquiry
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
     * Set uuid
     *
     * @param string $uuid
     *
     * @return TalentInquiry
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return TalentInquiry
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
     * Set talent
     *
     * @param \AppBundle\Entity\Talent $talent
     *
     * @return TalentInquiry
     */
    public function setTalent(\AppBundle\Entity\Talent $talent = null)
    {
        $this->talent = $talent;

        return $this;
    }

    /**
     * Get talent
     *
     * @return \AppBundle\Entity\Talent
     */
    public function getTalent()
    {
        return $this->talent;
    }

    /**
     * Set booking
     *
     * @param \AppBundle\Entity\TalentBooking $booking
     *
     * @return TalentInquiry
     */
    public function setBooking(\AppBundle\Entity\TalentBooking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \AppBundle\Entity\TalentBooking
     */
    public function getBooking()
    {
        return $this->booking;
    }
}
