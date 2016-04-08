<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TalentBookingCancel {
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="TalentBooking", inversedBy="cancels")
     */
    protected $talentBooking;
    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;
    /**
     * @ORM\Column(type="integer")
     */
    protected $provider;
    /**
     * @ORM\Column(type="string")
     */
    protected $reason;
    /**
     * @ORM\Column(type="string")
     */
    protected $description;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;
    

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
     * Set provider
     *
     * @param \bool $provider
     *
     * @return TalentBookingCancel
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \bool
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set reason
     *
     * @param string $reason
     *
     * @return TalentBookingCancel
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TalentBookingCancel
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
     * Set booking
     *
     * @param \AppBundle\Entity\TalentBooking $booking
     *
     * @return TalentBookingCancel
     */
    public function setBooking(\AppBundle\Entity\TalentBooking $booking = null)
    {
        $this->talentBooking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \AppBundle\Entity\TalentBooking
     */
    public function getBooking()
    {
        return $this->talentBooking;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return TalentBookingCancel
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
     * Set description
     *
     * @param string $description
     *
     * @return TalentBookingCancel
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set talentBooking
     *
     * @param \AppBundle\Entity\TalentBooking $talentBooking
     *
     * @return TalentBookingCancel
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
