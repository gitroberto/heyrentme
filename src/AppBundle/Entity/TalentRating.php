<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TalentRatingRepository")
 */
class TalentRating {
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Talent", inversedBy="ratings")
     * @ORM\JoinColumn(name="talent_id", referencedColumnName="id")
     */
    protected $talent;
    /**
     * @ORM\OneToOne(targetEntity="TalentBooking", inversedBy="rating")
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
     * @return TalentRating
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
     * @return TalentRating
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return TalentRating
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
     * Set talent
     *
     * @param \AppBundle\Entity\Talent $talent
     *
     * @return TalentRating
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
     * @return TalentRating
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
