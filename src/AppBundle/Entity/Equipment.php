<?php

namespace AppBundle\Entity;

use AppBundle\Utils\Utils;
use DateTime;
use \DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="EquipmentRepository")
 * @ORM\Table(name="equipment")
 */
class Equipment
{    
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
    protected $name;
    /**
     * @ORM\Column(type="string", length=2500)
     */
    protected $description;
    /**
     * @ORM\Column(type="decimal", scale=2, precision=10)
     */
    protected $price;
    /**
     * @ORM\Column(type="decimal", scale=10, precision=2)
     */
    protected $discount;
    /**
     * @ORM\Column(type="decimal", scale=10, precision=2)
     */
    protected $value;
    /**
     * @ORM\Column(type="decimal", scale=10, precision=2)
     */
    protected $deposit;
    /**
     * @ORM\Column(type="decimal", scale=10, precision=2)
     */
    protected $priceBuy;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $invoice;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $industrial;
    
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
     * @ORM\ManyToMany(targetEntity="Subcategory", inversedBy="equipments")
     * @ORM\JoinTable(name="equipment_subcategory")     
     */
    protected $subcategories;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="equipments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\OneToMany(targetEntity="EquipmentImage", mappedBy="equipment")
     */
    protected $images;
    
    /**
     * @ORM\OneToMany(targetEntity="Discount", mappedBy="equipment")
     */
    protected $discounts;
    
    /**
     * @ORM\OneToMany(targetEntity="EquipmentFeature", mappedBy="equipment")
     */
    protected $features;        
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $status;
    
    /**
     * @ORM\Column(type="string", length=500)
     */
    protected $reason;

    /**
     * @ORM\OneToOne(targetEntity="EquipmentAge")
     * @ORM\JoinColumn(name="equipment_age_id", referencedColumnName="id")
     */
    protected $age;
    
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
     * @ORM\Column(type="string")
     */
    protected $descType;
    /**
     * @ORM\Column(type="string")
     */
    protected $descSpecial;
    /**
     * @ORM\Column(type="string")
     */
    protected $descCondition;
    /**
     * @ORM\Column(type="decimal")
     */
    protected $rating;
    /**
     * @ORM\OneToMany(targetEntity="EquipmentRating", mappedBy="equipment")
     */
    protected $ratings;
    /**
     * @ORM\Column(type="integer")
     */
    protected $functional = 0; // default
    /**
     * @ORM\Column(type="integer")
     */
    protected $accept = 0; // default

    /**
     * @ORM\Column(type="string", length=36)
     */
    protected $uuid;
    /**
     * @ORM\Column(type="decimal", scale=2, precision=10)
     */
    protected $priceWeek;
    /**
     * @ORM\Column(type="decimal", scale=2, precision=10)
     */
    protected $priceMonth;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $service = 0;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $showcaseStart = 0;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $showcaseEquipment = 0;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $featured = false;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $testDrive = false;
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
        return "E-{$this->id}";
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
    
    public function IsNewOfferDiscountPossible() {        
        //TODO check if there is any rating for this equipment and add condition about active discount!
        return ($this->getActiveDiscount() && $this->getActiveDiscount()->getType() == 1) || 
                    !$this->getActiveDiscount();
    }
    
    public function IsTemporaryDiscountPossible() {
        $discounts = $this->getDiscounts();
        $discountsInLastMonth = 0;
        $now = new DateTime();
        $lastMonth = $now->sub(new DateInterval("P1M"))->getTimestamp();        
        foreach ($discounts as $dc) {            
            //$start = $dc->getCreatedAt()->getTimestamp();
            $end = $dc->getExpiresAt()->getTimestamp();
            if ($lastMonth <= $end) {
                $discountsInLastMonth++;
            }
        }
        return $discountsInLastMonth < 2 && (($this->getActiveDiscount() && $this->getActiveDiscount()->getType() == 2) || 
                    !$this->getActiveDiscount());
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
    
    public function getPricesLine() {
        if ($this->priceMonth) {
            $priceToDisplay = $this->priceMonth/30;
        } else if ($this->priceWeek){
            $priceToDisplay = $this->priceWeek/7;
        } else {
            $priceToDisplay = $this->price;
        }
        return "ab " . number_format(round($priceToDisplay, 0), 2, ",", " ");
    }
    public function getPricesDesc() {       
        $arr = array();
        array_push($arr, "T");
        $p = $this->getPriceWeek();
        if ($p !== null && $p > 0)
            array_push($arr, "W");
        $p = $this->getPriceMonth();
        if ($p !== null && $p > 0)
            array_push($arr, "M");
        return implode("/", $arr);
    }
    public function getDetailPricesLine() {
        $arr = array();
        
        $s = sprintf("T&nbsp;%.2f", $this->getPrice());
        $s = str_replace('.', ',', $s);
        array_push($arr, $s);
        $p = $this->getPriceWeek();
        if ($p !== null && $p > 0) {
            $s = sprintf("W&nbsp;%.2f", $p);
            $s = str_replace('.', ',', $s);
            array_push($arr, $s);
        }
        $p = $this->getPriceMonth();
        if ($p !== null && $p > 0) {
            $s = sprintf("M&nbsp;%.2f", $p);
            $s = str_replace('.', ',', $s);
            array_push($arr, $s);
        }
        return implode("&nbsp;/&nbsp;", $arr);
    }
    
    public function calculatePrice($days) {
        $pm = $this->priceMonth;
        if ($days >= 30 && $pm !== null && $pm > 0)
            return $days * $pm / 30.0;
        $pw = $this->priceWeek;
        if ($days >= 7 && $pw !== null && $pw > 0)
            return $days * $pw / 7.0;
        if ($this->price === null)
            return null;
        else
            return $days * $this->price;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = Equipment::STATUS_INCOMPLETE;
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
    
    #dummy method
    public function setId($id)
    {
        return $this;
    }
    
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }
     public function getReason()
    {
        return $this->reason;
    }
    
    public function setOfferStatus($offerStatus)
    {
        $this->offerStatus = $offerStatus;
        return $this;
    }
     public function getOfferStatus()
    {
        return $this->offerStatus;
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
     public function getStatus()
    {
        return $this->status;
    }
    
    public function getStatusStr() {
        switch ($this->status) {
            case self::STATUS_NEW: return "new";
            case self::STATUS_MODIFIED: return "modified";
            case self::STATUS_APPROVED: return "approved";
            case self::STATUS_REJECTED: return "rejected";
            case self::STATUS_INCOMPLETE: return "incomplete";
            default:
                throw new RuntimeException("Equipment status corrupt!");
        }
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Equipment
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
     * Set description
     *
     * @param string $description
     *
     * @return Equipment
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
     * @return Equipment
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
     * Set discount
     *
     * @param string $discount
     *
     * @return Equipment
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return string
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Equipment
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set deposit
     *
     * @param string $deposit
     *
     * @return Equipment
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
     * @return Equipment
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
     * Set invoice
     *
     * @param integer $invoice
     *
     * @return Equipment
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return integer
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set industrial
     *
     * @param integer $industrial
     *
     * @return Equipment
     */
    public function setIndustrial($industrial)
    {
        $this->industrial = $industrial;

        return $this;
    }

    /**
     * Get industrial
     *
     * @return integer
     */
    public function getIndustrial()
    {
        return $this->industrial;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Equipment
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    
    /**
     * Set addrStreet
     *
     * @param string $addrStreet
     *
     * @return Equipment
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
     * @return Equipment
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
    public function getAddrFlatNumber()
    {
        return $this->addrFlatNumber;
    }

    /**
     * Set addrNumber
     *
     * @param string $addrNumber
     *
     * @return Equipment
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
    public function getAddrNumber()
    {
        return $this->addrNumber;
    }

    /**
     * Set addrPostcode
     *
     * @param string $addrPostcode
     *
     * @return Equipment
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
     * @return Equipment
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
     * Add feature
     *
     * @param EquipmentFeature $feature
     *
     * @return Equipment
     */
    public function addFeature(EquipmentFeature $feature)
    {
        $this->features[] = $feature;

        return $this;
    }

    /**
     * Remove feature
     *
     * @param EquipmentFeature $feature
     */
    public function removeFeature(EquipmentFeature $feature)
    {
        $this->features->removeElement($feature);
    }

    /**
     * Get features
     *
     * @return Collection
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     *
     * @return Equipment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @param DateTime $modifiedAt
     *
     * @return Equipment
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @return DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Add discount
     *
     * @param Discount $discount
     *
     * @return Equipment
     */
    public function addDiscount(Discount $discount)
    {
        $this->discounts[] = $discount;

        return $this;
    }

    /**
     * Remove discount
     *
     * @param Discount $discount
     */
    public function removeDiscount(Discount $discount)
    {
        $this->discounts->removeElement($discount);
    }

    /**
     * Get discounts
     *
     * @return Collection
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }
    
    public function checkStatusOnSave(){
        if ($this->status == Equipment::STATUS_APPROVED || $this->status == Equipment::STATUS_REJECTED) {            
            $this->changeStatus(Equipment::STATUS_MODIFIED, null);
            return true;
        }
        return false;
    }
    
    public function changeStatus($newStatus, $reason){
        
        #TODO: Move status management code (emails etc) into one method
        
        $this->status = $newStatus;
        switch($newStatus){
            case Equipment::STATUS_APPROVED:
            case Equipment::STATUS_REJECTED:
                #$mailer = $this->get('fos_user.mailer');
                #$mailer->sendNewModifiedEquipmentInfoMessage($this);                
                break;            
            case Equipment::STATUS_INCOMPLETE:
                break;
            case Equipment::STATUS_NEW:
            case Equipment::STATUS_MODIFIED:
                #$mailer = $this->get('fos_user.mailer');
                #$mailer->sendNewModifiedEquipmentInfoMessage($this);                
                break;
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
     * Set age
     *
     * @param \AppBundle\Entity\EquipmentAge $age
     *
     * @return Equipment
     */
    public function setAge(\AppBundle\Entity\EquipmentAge $age = null)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return \AppBundle\Entity\EquipmentAge
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set timeMorning
     *
     * @param boolean $timeMorning
     *
     * @return Equipment
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
     * @return Equipment
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
     * @return Equipment
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
     * @return Equipment
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
     * Set descType
     *
     * @param string $descType
     *
     * @return Equipment
     */
    public function setDescType($descType)
    {
        $this->descType = $descType;

        return $this;
    }

    /**
     * Get descType
     *
     * @return string
     */
    public function getDescType()
    {
        return $this->descType;
    }

    /**
     * Set descSpecial
     *
     * @param string $descSpecial
     *
     * @return Equipment
     */
    public function setDescSpecial($descSpecial)
    {
        $this->descSpecial = $descSpecial;

        return $this;
    }

    /**
     * Get descSpecial
     *
     * @return string
     */
    public function getDescSpecial()
    {
        return $this->descSpecial;
    }

    /**
     * Set descCondition
     *
     * @param string $descCondition
     *
     * @return Equipment
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
     * Set rating
     *
     * @param string $rating
     *
     * @return Equipment
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
     * Add rating
     *
     * @param \AppBundle\Entity\EquipmentRating $rating
     *
     * @return Equipment
     */
    public function addRating(\AppBundle\Entity\EquipmentRating $rating)
    {
        $this->ratings[] = $rating;

        return $this;
    }

    /**
     * Remove rating
     *
     * @param \AppBundle\Entity\EquipmentRating $rating
     */
    public function removeRating(\AppBundle\Entity\EquipmentRating $rating)
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
     * Add image
     *
     * @param \AppBundle\Entity\EquipmentImage $image
     *
     * @return Equipment
     */
    public function addImage(\AppBundle\Entity\EquipmentImage $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\EquipmentImage $image
     */
    public function removeImage(\AppBundle\Entity\EquipmentImage $image)
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

    protected $equipmentImages;
    public function getEquipmentImages() {
        return $this->equipmentImages;
    }
    public function setEquipmentImages($equipmentImages) {
        $this->equipmentImages = $equipmentImages;
    }    

    /**
     * Set functional
     *
     * @param integer $functional
     *
     * @return Equipment
     */
    public function setFunctional($functional)
    {
        $this->functional = $functional;

        return $this;
    }

    /**
     * Get functional
     *
     * @return integer
     */
    public function getFunctional()
    {
        return $this->functional;
    }

    /**
     * Set accept
     *
     * @param integer $accept
     *
     * @return Equipment
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
     * Set priceWeek
     *
     * @param string $priceWeek
     *
     * @return Equipment
     */
    public function setPriceWeek($priceWeek)
    {
        $this->priceWeek = $priceWeek;

        return $this;
    }

    /**
     * Get priceWeek
     *
     * @return string
     */
    public function getPriceWeek()
    {
        return $this->priceWeek;
    }

    /**
     * Set priceMonth
     *
     * @param string $priceMonth
     *
     * @return Equipment
     */
    public function setPriceMonth($priceMonth)
    {
        $this->priceMonth = $priceMonth;

        return $this;
    }

    /**
     * Get priceMonth
     *
     * @return string
     */
    public function getPriceMonth()
    {
        return $this->priceMonth;
    }

    /**
     * Set service
     *
     * @param integer $service
     *
     * @return Equipment
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return integer
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set showcaseStart
     *
     * @param boolean $showcaseStart
     *
     * @return Equipment
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
     * Set showcaseEquipment
     *
     * @param boolean $showcaseEquipment
     *
     * @return Equipment
     */
    public function setShowcaseEquipment($showcaseEquipment)
    {
        $this->showcaseEquipment = $showcaseEquipment;

        return $this;
    }

    /**
     * Get showcaseEquipment
     *
     * @return boolean
     */
    public function getShowcaseEquipment()
    {
        return $this->showcaseEquipment;
    }

    /**
     * Set featured
     *
     * @param boolean $featured
     *
     * @return Equipment
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
     * Set testDrive
     *
     * @param boolean $testDrive
     *
     * @return Equipment
     */
    public function setTestDrive($testDrive)
    {
        $this->testDrive = $testDrive;

        return $this;
    }

    /**
     * Get testDrive
     *
     * @return boolean
     */
    public function getTestDrive()
    {
        return $this->testDrive;
    }

    /**
     * Set inquiryEmail
     *
     * @param string $inquiryEmail
     *
     * @return Equipment
     */
    public function setInquiryEmail($inquiryEmail)
    {
        $this->inquiryEmail = $inquiryEmail;

        return $this;
    }

    /**
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
     * @return Equipment
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
