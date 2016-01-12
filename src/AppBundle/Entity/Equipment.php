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
     * @ORM\Column(type="string", length=256)
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
     * @ORM\Column(type="string", length=4)
     */
    protected $addrPostcode;
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $addrPlace;    
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
    
    public function getUrlPath() {
       $s = Utils::slugify($this->getName());
       return "{$this->id}/{$s}";
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
        return sprintf("%s %s, %s %s", $this->addrStreet, $this->addrNumber, $this->addrPostcode, $this->addrPlace);
    }
    public function getIncompleteAddressAsString() {
        return sprintf("%s, %s %s", $this->addrStreet, $this->addrPostcode, $this->addrPlace);
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new ArrayCollection();
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
     * Set subcategory
     *
     * @param Subcategory $subcategory
     *
     * @return Equipment
     */
    public function setSubcategory(Subcategory $subcategory = null)
    {
        $this->subcategory = $subcategory;

        return $this;
    }

    /**
     * Get subcategory
     *
     * @return Subcategory
     */
    public function getSubcategory()
    {
        return $this->subcategory;
    }

    /**
     * Add image
     *
     * @param Image $image
     *
     * @return Equipment
     */
    public function addImage(Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param Image $image
     */
    public function removeImage(Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return Collection
     */
    public function getImages()
    {
        return $this->images;
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
        }
    }
    
    public function changeStatus($newStatus, $reason){
        
        
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
}
