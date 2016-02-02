<?php

namespace AppBundle\Entity;

use AppBundle\Utils\Utils;
use DateTime;
use \DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="TalentRepository")
 */
class Talent {    
    
    const STATUS_NEW = 1;
    const STATUS_MODIFIED = 2;
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = 4;  
    const STATUS_INCOMPLETE = 5;
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=256)
     */
    protected $description;
    /**
     * @ORM\Column(type="decimal", scale=2, precision=10)
     */
    protected $price;
    
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $addrStreet;
    /**
     * @ORM\Column(type="string", length=16)
     */
    protected $addrNumber;
    /**
     * @ORM\Column(type="string", length=4)
     */
    protected $addrPostcode;
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $addrPlace;    
    /**
     * @ORM\Column(type="string", length=500)
     */
    protected $offerStatus; 
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;
            
    /**
     * @ORM\ManyToOne(targetEntity="Subcategory", inversedBy="equipments")
     * @ORM\JoinColumn(name="subcategory_id", referencedColumnName="id")
     */
    protected $subcategory;
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="equipments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * 
     * @ORM\ManyToMany(targetEntity="Image")
     * @ORM\JoinTable(name="equipment_image",
     *      joinColumns={ @ORM\JoinColumn(name="equipment_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="image_id", referencedColumnName="id") }
     *  )
     */
    protected $images;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $status;
    
    /**
     * @ORM\Column(type="string", length=500)
     */
    protected $reason;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $timeMorning;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $timeAfternoon;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $timeEvening;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $timeWeekend;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $optClient;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $optGroup;
    /**
     * @ORM\Column(type="string")
     */
    protected $descReference;
    /**
     * @ORM\Column(type="string")
     */
    protected $descScope;
    /**
     * @ORM\Column(type="string")
     */
    protected $descCondition;
    /**
     * @ORM\Column(type="string")
     */
    protected $descTarget;
    /**
     * @ORM\Column(type="decimal")
     */
    protected $rating;
    /**
     * @ORM\OneToMany(targetEntity="TalentRating", mappedBy="talent")
     */
    protected $ratings;

    
    
    public function getUrlPath() {
       $s = Utils::slugify($this->getName());
       return "{$this->id}/{$s}";
    }
    
    
    public function getActivePrice() {
        $d = $this->getActiveDiscount();
        if ($d != null) {
            $p = $this->getPrice() * (100.0 - $d->getPercent()) / 100.0;
        }
        else {
            $p = $this->getPrice();
        }
        return $p;
    }
        
    public function getAddressAsString() {
        return sprintf("%s %s, %s %s", $this->addrStreet, $this->addrNumber, $this->addrPostcode, $this->addrPlace);
    }
    public function getIncompleteAddressAsString() {
        return sprintf("%s, %s %s", $this->addrStreet, $this->addrPostcode, $this->addrPlace);
    }
        
    public function getStatusStr() {
        switch ($this->status) {
            case self::STATUS_NEW: return "new";
            case self::STATUS_MODIFIED: return "modified";
            case self::STATUS_APPROVED: return "approved";
            case self::STATUS_REJECTED: return "rejected";
            case self::STATUS_INCOMPLETE: return "incomplete";
            default:
                throw new RuntimeException("Talent status corrupt!");
        }
    }


    public function getTimeAsString() {
        $arr = array();
        if ($this->getTimeMorning()) {
            array_push($arr, 'Nachm.');
        }
        if ($this->getTimeAfternoon()) {
            array_push($arr, 'Vorm.');
        }
        if ($this->getTimeEvening()) {
            array_push($arr, 'Abends');
        }
        if ($this->getTimeWeekend()) {
            array_push($arr, 'WE');
        }
        return implode(" / ", $arr);
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ratings = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set description
     *
     * @param string $description
     *
     * @return Talent
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
     * Set price
     *
     * @param string $price
     *
     * @return Talent
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
     * Set addrStreet
     *
     * @param string $addrStreet
     *
     * @return Talent
     */
    public function setAddrStreet($addrStreet)
    {
        $this->addrStreet = $addrStreet;

        return $this;
    }

    /**
     * Get addrStreet
     *
     * @return string
     */
    public function getAddrStreet()
    {
        return $this->addrStreet;
    }

    /**
     * Set addrNumber
     *
     * @param string $addrNumber
     *
     * @return Talent
     */
    public function setAddrNumber($addrNumber)
    {
        $this->addrNumber = $addrNumber;

        return $this;
    }

    /**
     * Get addrNumber
     *
     * @return string
     */
    public function getAddrNumber()
    {
        return $this->addrNumber;
    }

    /**
     * Set addrPostcode
     *
     * @param string $addrPostcode
     *
     * @return Talent
     */
    public function setAddrPostcode($addrPostcode)
    {
        $this->addrPostcode = $addrPostcode;

        return $this;
    }

    /**
     * Get addrPostcode
     *
     * @return string
     */
    public function getAddrPostcode()
    {
        return $this->addrPostcode;
    }

    /**
     * Set addrPlace
     *
     * @param string $addrPlace
     *
     * @return Talent
     */
    public function setAddrPlace($addrPlace)
    {
        $this->addrPlace = $addrPlace;

        return $this;
    }

    /**
     * Get addrPlace
     *
     * @return string
     */
    public function getAddrPlace()
    {
        return $this->addrPlace;
    }

    /**
     * Set offerStatus
     *
     * @param string $offerStatus
     *
     * @return Talent
     */
    public function setOfferStatus($offerStatus)
    {
        $this->offerStatus = $offerStatus;

        return $this;
    }

    /**
     * Get offerStatus
     *
     * @return string
     */
    public function getOfferStatus()
    {
        return $this->offerStatus;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Talent
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
     * @return Talent
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
     * Set status
     *
     * @param integer $status
     *
     * @return Talent
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
     * Set reason
     *
     * @param string $reason
     *
     * @return Talent
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
     * Set timeMorning
     *
     * @param boolean $timeMorning
     *
     * @return Talent
     */
    public function setTimeMorning($timeMorning)
    {
        $this->timeMorning = $timeMorning;

        return $this;
    }

    /**
     * Get timeMorning
     *
     * @return boolean
     */
    public function getTimeMorning()
    {
        return $this->timeMorning;
    }

    /**
     * Set timeAfternoon
     *
     * @param boolean $timeAfternoon
     *
     * @return Talent
     */
    public function setTimeAfternoon($timeAfternoon)
    {
        $this->timeAfternoon = $timeAfternoon;

        return $this;
    }

    /**
     * Get timeAfternoon
     *
     * @return boolean
     */
    public function getTimeAfternoon()
    {
        return $this->timeAfternoon;
    }

    /**
     * Set timeEvening
     *
     * @param boolean $timeEvening
     *
     * @return Talent
     */
    public function setTimeEvening($timeEvening)
    {
        $this->timeEvening = $timeEvening;

        return $this;
    }

    /**
     * Get timeEvening
     *
     * @return boolean
     */
    public function getTimeEvening()
    {
        return $this->timeEvening;
    }

    /**
     * Set timeWeekend
     *
     * @param boolean $timeWeekend
     *
     * @return Talent
     */
    public function setTimeWeekend($timeWeekend)
    {
        $this->timeWeekend = $timeWeekend;

        return $this;
    }

    /**
     * Get timeWeekend
     *
     * @return boolean
     */
    public function getTimeWeekend()
    {
        return $this->timeWeekend;
    }

    /**
     * Set optClient
     *
     * @param boolean $optClient
     *
     * @return Talent
     */
    public function setOptClient($optClient)
    {
        $this->optClient = $optClient;

        return $this;
    }

    /**
     * Get optClient
     *
     * @return boolean
     */
    public function getOptClient()
    {
        return $this->optClient;
    }

    /**
     * Set optGroup
     *
     * @param boolean $optGroup
     *
     * @return Talent
     */
    public function setOptGroup($optGroup)
    {
        $this->optGroup = $optGroup;

        return $this;
    }

    /**
     * Get optGroup
     *
     * @return boolean
     */
    public function getOptGroup()
    {
        return $this->optGroup;
    }

    /**
     * Set descReference
     *
     * @param string $descReference
     *
     * @return Talent
     */
    public function setDescReference($descReference)
    {
        $this->descReference = $descReference;

        return $this;
    }

    /**
     * Get descReference
     *
     * @return string
     */
    public function getDescReference()
    {
        return $this->descReference;
    }

    /**
     * Set descScope
     *
     * @param string $descScope
     *
     * @return Talent
     */
    public function setDescScope($descScope)
    {
        $this->descScope = $descScope;

        return $this;
    }

    /**
     * Get descScope
     *
     * @return string
     */
    public function getDescScope()
    {
        return $this->descScope;
    }

    /**
     * Set descCondition
     *
     * @param string $descCondition
     *
     * @return Talent
     */
    public function setDescCondition($descCondition)
    {
        $this->descCondition = $descCondition;

        return $this;
    }

    /**
     * Get descCondition
     *
     * @return string
     */
    public function getDescCondition()
    {
        return $this->descCondition;
    }

    /**
     * Set descTarget
     *
     * @param string $descTarget
     *
     * @return Talent
     */
    public function setDescTarget($descTarget)
    {
        $this->descTarget = $descTarget;

        return $this;
    }

    /**
     * Get descTarget
     *
     * @return string
     */
    public function getDescTarget()
    {
        return $this->descTarget;
    }

    /**
     * Set rating
     *
     * @param string $rating
     *
     * @return Talent
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return string
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set subcategory
     *
     * @param \AppBundle\Entity\Subcategory $subcategory
     *
     * @return Talent
     */
    public function setSubcategory(\AppBundle\Entity\Subcategory $subcategory = null)
    {
        $this->subcategory = $subcategory;

        return $this;
    }

    /**
     * Get subcategory
     *
     * @return \AppBundle\Entity\Subcategory
     */
    public function getSubcategory()
    {
        return $this->subcategory;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Talent
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
     * Add image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return Talent
     */
    public function addImage(\AppBundle\Entity\Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\Image $image
     */
    public function removeImage(\AppBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add rating
     *
     * @param \AppBundle\Entity\TalentRating $rating
     *
     * @return Talent
     */
    public function addRating(\AppBundle\Entity\TalentRating $rating)
    {
        $this->ratings[] = $rating;

        return $this;
    }

    /**
     * Remove rating
     *
     * @param \AppBundle\Entity\TalentRating $rating
     */
    public function removeRating(\AppBundle\Entity\TalentRating $rating)
    {
        $this->ratings->removeElement($rating);
    }

    /**
     * Get ratings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRatings()
    {
        return $this->ratings;
    }
}
