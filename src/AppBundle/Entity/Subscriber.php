<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SubscriberRepository")
 * @ORM\Table(name="subscriber")
 */
class Subscriber {
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string")
     */
    protected $email;
    /**
     * @ORM\Column(type="string")
     */
    protected $token;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $confirmed = 0;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $unsubscribed = 0;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $unsubscribedAt;
    /**
     * @ORM\OneToMany(targetEntity="DiscountCode", mappedBy="subscriber")
     */
    protected $discountCodes;

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
     * Set email
     *
     * @param string $email
     *
     * @return Subscriber
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
     * Set token
     *
     * @param string $token
     *
     * @return Subscriber
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return Subscriber
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Subscriber
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
     * @return Subscriber
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
     * Set unsubscribed
     *
     * @param boolean $unsubscribed
     *
     * @return Subscriber
     */
    public function setUnsubscribed($unsubscribed)
    {
        $this->unsubscribed = $unsubscribed;

        return $this;
    }

    /**
     * Get unsubscribed
     *
     * @return boolean
     */
    public function getUnsubscribed()
    {
        return $this->unsubscribed;
    }

    /**
     * Set unsubscribedAt
     *
     * @param \DateTime $unsubscribedAt
     *
     * @return Subscriber
     */
    public function setUnsubscribedAt($unsubscribedAt)
    {
        $this->unsubscribedAt = $unsubscribedAt;

        return $this;
    }

    /**
     * Get unsubscribedAt
     *
     * @return \DateTime
     */
    public function getUnsubscribedAt()
    {
        return $this->unsubscribedAt;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->discountCodes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add discountCode
     *
     * @param \AppBundle\Entity\DiscountCode $discountCode
     *
     * @return Subscriber
     */
    public function addDiscountCode(\AppBundle\Entity\DiscountCode $discountCode)
    {
        $this->discountCodes[] = $discountCode;

        return $this;
    }

    /**
     * Remove discountCode
     *
     * @param \AppBundle\Entity\DiscountCode $discountCode
     */
    public function removeDiscountCode(\AppBundle\Entity\DiscountCode $discountCode)
    {
        $this->discountCodes->removeElement($discountCode);
    }

    /**
     * Get discountCodes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDiscountCodes()
    {
        return $this->discountCodes;
    }
}
