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
     * @ORM\Column(type="string", length=50)
     */
    protected $name;    
    /**
     * @ORM\Column(type="string", length=10000)
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
     * @ORM\Column(type="string", length=16)
     */
    protected $addrFlatNumber;
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
     * @ORM\ManyToMany(targetEntity="Subcategory", inversedBy="talents")
     * @ORM\JoinTable(name="talent_subcategory")     
     */
    protected $subcategories;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="talents")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\OneToMany(targetEntity="TalentImage", mappedBy="talent")
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
    /**
     * @ORM\OneToMany(targetEntity="TalentTariff", mappedBy="talent")
     */
    protected $tariffs;
    
    /**
     * @ORM\OneToOne(targetEntity="Video")
     * @ORM\JoinColumn(name="video_id", referencedColumnName="id")
     */
    protected $video;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $licence = 0; // default value
    /**
     * @ORM\Column(type="integer")
     */
    protected $accept = 0; // default value
    /**
     * @ORM\Column(type="integer")
     */
    protected $requestPrice = 0; // default value

    /**
     * @ORM\Column(type="string", length=36)
     */
    protected $uuid;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $showcaseStart = 0;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $showcaseTalent = 0;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $featured = false;
    /**
     * @ORM\Column(type="string")
     */
    protected $inquiryEmail;
    
    
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    
    public function getUuid()
    {
        return $this->uuid;
    }
    
    public function getUfid() { // user friendly id
        return "T-{$this->id}";
    }
    
    public function getUrlPath() {
       $slug = Utils::slugify($this->getName());
       return sprintf("%s/%s", $this->getUfid(), $slug);
    }
    
    public function getActiveDiscount() {
        $discounts = $this->getDiscounts();
        foreach ($discounts as $dc) {
            $now = (new DateTime())->getTimestamp();
            $start = $dc->getCreatedAt()->getTimestamp();
            $end = $dc->getExpiresAt()->getTimestamp();
            if ($start <= $now and $now <= $end) {
                return $dc;
            }
        }
        return null;
    }
    
    public function getActivePrice() {
//        $d = $this->getActiveDiscount();
//        if ($d != null) {
//            $p = $this->getPrice() * (100.0 - $d->getPercent()) / 100.0;
//        }
//        else {
            $p = $this->getPrice();
//        }
        return $p;
    }
        
    public function getAddressAsString() {
        $fn = '';
        if (!empty($this->addrFlatNumber)) {
            $fn = '/' . $this->addrFlatNumber;
        }
        return sprintf("%s %s%s, %s %s", $this->addrStreet, $this->addrNumber, $fn, $this->addrPostcode, $this->addrPlace);
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
    
    public function getFirstTariff() {
        $arr = $this->tariffs->toArray();
        usort($arr, function($a, $b) {
            $d = $a->getPosition() - $b->getPosition();
            return $d === 0 ? 0 : ($d > 0 ? 1 : -1);
        });
        return $arr[0];
    }    

    public function checkStatusOnSave(){
        if ($this->status == Talent::STATUS_APPROVED || $this->status == Talent::STATUS_REJECTED) {            
            $this->changeStatus(Talent::STATUS_MODIFIED, null);
            return true;
        }
        return false;
    }
    public function changeStatus($newStatus, $reason){
        
        #TODO: Move status management code (emails etc) into one method
        
        $this->status = $newStatus;
        switch($newStatus){
            case Talent::STATUS_APPROVED:
            case Talent::STATUS_REJECTED:
                #$mailer = $this->get('fos_user.mailer');
                #$mailer->sendNewModifiedTalentInfoMessage($this);                
                break;            
            case Talent::STATUS_INCOMPLETE:
                break;
            case Talent::STATUS_NEW:
            case Talent::STATUS_MODIFIED:
                #$mailer = $this->get('fos_user.mailer');
                #$mailer->sendNewModifiedTalentInfoMessage($this);                
                break;
        }
        
    }


    public function getTimeAsString() {
        $arr = array();
        if ($this->getTimeMorning()) {
            array_push($arr, 'Vorm.');
        }
        if ($this->getTimeAfternoon()) {
            array_push($arr, 'Nachm.');
        }
        if ($this->getTimeEvening()) {
            array_push($arr, 'Abends');
        }
        if ($this->getTimeWeekend()) {
            array_push($arr, 'WE');
        }
        return implode(" / ", $arr);
    }
    public function getSubcategoriesAsString() {
        return implode(", ", array_map(function($i) { return $i->getName(); }, $this->subcategories->toArray()));
    }
    
    public function anyCategoryActive() {
        foreach($this->subcategories as $sc){
            $cat = $sc->getCategory();
            if ($cat && $cat->getActive() == 1){
                return true;
            }
        }
        return false;
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
    public function setId($id)
    {
        // does nothing
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
     * Set addrNumber
     *
     * @param string $addrNumber
     *
     * @return Talent
     */
    public function setAddrFlatNumber($addrFlatNumber)
    {
        $this->addrFlatNumber = $addrFlatNumber;

        return $this;
    }

    /**
     * Get addrNumber
     *
     * @return string
     */
    public function getAddrFlatNumber()
    {
        return $this->addrFlatNumber;
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

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Talent
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
     * Set video
     *
     * @param \AppBundle\Entity\Video $video
     *
     * @return Talent
     */
    public function setVideo(\AppBundle\Entity\Video $video = null)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return \AppBundle\Entity\Video
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Add image
     *
     * @param \AppBundle\Entity\TalentImage $image
     *
     * @return Talent
     */
    public function addImage(\AppBundle\Entity\TalentImage $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\TalentImage $image
     */
    public function removeImage(\AppBundle\Entity\TalentImage $image)
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
    protected $talentImages;
    public function getTalentImages() {
        return $this->talentImages;
    }
    public function setTalentImages($talentImages) {
        $this->talentImages = $talentImages;
    }    

    /**
     * Set licence
     *
     * @param integer $licence
     *
     * @return Talent
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get licence
     *
     * @return integer
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set accept
     *
     * @param integer $accept
     *
     * @return Talent
     */
    public function setAccept($accept)
    {
        $this->accept = $accept;

        return $this;
    }

    /**
     * Get accept
     *
     * @return integer
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * Set requestPrice
     *
     * @param integer $requestPrice
     *
     * @return Talent
     */
    public function setRequestPrice($requestPrice)
    {
        $this->requestPrice = $requestPrice;

        return $this;
    }

    /**
     * Get requestPrice
     *
     * @return integer
     */
    public function getRequestPrice()
    {
        return $this->requestPrice;
    }

    /**
     * Set showcaseStart
     *
     * @param boolean $showcaseStart
     *
     * @return Talent
     */
    public function setShowcaseStart($showcaseStart)
    {
        $this->showcaseStart = $showcaseStart;

        return $this;
    }

    /**
     * Get showcaseStart
     *
     * @return boolean
     */
    public function getShowcaseStart()
    {
        return $this->showcaseStart;
    }

    /**
     * Set showcaseTalent
     *
     * @param boolean $showcaseTalent
     *
     * @return Talent
     */
    public function setShowcaseTalent($showcaseTalent)
    {
        $this->showcaseTalent = $showcaseTalent;

        return $this;
    }

    /**
     * Get showcaseTalent
     *
     * @return boolean
     */
    public function getShowcaseTalent()
    {
        return $this->showcaseTalent;
    }

    /**
     * Set featured
     *
     * @param boolean $featured
     *
     * @return Talent
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;

        return $this;
    }

    /**
     * Get featured
     *
     * @return boolean
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Add tariff
     *
     * @param \AppBundle\Entity\TalentTariff $tariff
     *
     * @return Talent
     */
    public function addTariff(\AppBundle\Entity\TalentTariff $tariff)
    {
        $this->tariffs[] = $tariff;
    }
    /*
     * Set inquiryEmail
     *
     * @param string $inquiryEmail
     *
     * @return Talent
     */
    public function setInquiryEmail($inquiryEmail)
    {
        $this->inquiryEmail = $inquiryEmail;
        return $this;
    }

    /**
     * Remove tariff
     *
     * @param \AppBundle\Entity\TalentTariff $tariff
     */
    public function removeTariff(\AppBundle\Entity\TalentTariff $tariff)
    {
        $this->tariffs->removeElement($tariff);
    }

    /**
     * Get tariffs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTariffs()
    {
        return $this->tariffs;
    }
    /*
     * Get inquiryEmail
     *
     * @return string
     */
    public function getInquiryEmail()
    {
        return $this->inquiryEmail;
    }

    /**
     * Add subcategory
     *
     * @param \AppBundle\Entity\Subcategory $subcategory
     *
     * @return Talent
     */
    public function addSubcategory(\AppBundle\Entity\Subcategory $subcategory)
    {
        $this->subcategories[] = $subcategory;

        return $this;
    }

    /**
     * Remove subcategory
     *
     * @param \AppBundle\Entity\Subcategory $subcategory
     */
    public function removeSubcategory(\AppBundle\Entity\Subcategory $subcategory)
    {
        $this->subcategories->removeElement($subcategory);
    }

    /**
     * Get subcategories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubcategories()
    {
        return $this->subcategories;
    }
}
