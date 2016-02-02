<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EquipmentRatingRepository")
 */
class EquipmentRating {
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Equipment", inversedBy="ratings")
     * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id")
     */
    protected $equipment;
    /**
     * @ORM\OneToOne(targetEntity="EquipmentBooking")
     */
    protected $booking;
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
     * @return EquipmentRating
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
     * @return EquipmentRating
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
     * Set equipment
     *
     * @param \AppBundle\Entity\Equipment $equipment
     *
     * @return EquipmentRating
     */
    public function setEquipment(\AppBundle\Entity\Equipment $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get equipment
     *
     * @return \AppBundle\Entity\Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return EquipmentRating
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
     * @return EquipmentRating
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
}
