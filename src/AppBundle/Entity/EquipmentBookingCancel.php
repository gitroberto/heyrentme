<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EquipmentBookingCancel {
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="EquipmentBooking")
     */
    protected $booking;
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
     * @return EquipmentBookingCancel
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
     * @return EquipmentBookingCancel
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
     * @return EquipmentBookingCancel
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
     * @param \AppBundle\Entity\EquipmentBooking $booking
     *
     * @return EquipmentBookingCancel
     */
    public function setBooking(\AppBundle\Entity\EquipmentBooking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \AppBundle\Entity\EquipmentBooking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return EquipmentBookingCancel
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
     * @return EquipmentBookingCancel
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
}
