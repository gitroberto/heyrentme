<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class UserRating {
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ratings")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * @ORM\OneToOne(targetEntity="EquipmentBooking", inversedBy="userRating")
     */
    protected $booking;
    /**
     * @ORM\OneToOne(targetEntity="TalentBooking")
     */
    protected $talentBooking;
    /**
     * @ORM\Column(type="integer")
     */
    protected $rating;
    /**
     * @ORM\Column(type="string")
     */
    protected $opinion;
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
     * Set rating
     *
     * @param integer $rating
     *
     * @return UserRating
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
     * Set opinion
     *
     * @param string $opinion
     *
     * @return UserRating
     */
    public function setOpinion($opinion)
    {
        $this->opinion = $opinion;

        return $this;
    }

    /**
     * Get opinion
     *
     * @return string
     */
    public function getOpinion()
    {
        return $this->opinion;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserRating
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return UserRating
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
     * @return UserRating
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
     * Set talentBooking
     *
     * @param \AppBundle\Entity\TalentBooking $talentBooking
     *
     * @return UserRating
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
