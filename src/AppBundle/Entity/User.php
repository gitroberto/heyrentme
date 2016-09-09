<?php
namespace AppBundle\Entity;
// src/AppBundle/Entity/User.php
#whole class added by Seba


use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    const STATUS_OK = 1;
    const STATUS_BLOCKED = 2;    
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
      /**
     * @var string
     *
     * @ORM\Column(name="facebookID", type="string", nullable=true)
     */
    protected $facebookID;
    /**
     * @ORM\Column(type="decimal")
     */
    protected $rating;
    
    public function getFacebookID()
    {
        return $this->facebookID;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        $this->setUsername($email);
    }
    
    public function setFacebookID($facebookID)
    {
        $this->facebookID = $facebookID;
    }
    
    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", nullable=true)
     * 
     * 
     *  * @Assert\NotBlank(message="Please enter your name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max=128,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     * 
     */
    protected $name;
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $status;
    
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
            case self::STATUS_OK: return "OK";
            case self::STATUS_BLOCKED: return "BLOCKED";            
            default:
                throw new RuntimeException("User status corrupt!");
        }
    }
    
    /**
     * @var string
     *
     * @ORM\Column(name="Surname", type="string", nullable=true)
     * 
     * @Assert\NotBlank(message="Please enter your surname.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max=128,
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     * 
     */
    protected $surname;
    
    public function getSurname()
    {
        return $this->surname;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
    }
    
    /**
     * @var bit
     *     
     * 
     * @Assert\NotBlank(message="Please accept your surname.", groups={"Registration", "FacebookRegistration"})         
     * 
     */
    protected $accept;
    
    public function getAccept()
    {
        return $this->accept;
    }

    public function setAccept($accept)
    {
        $this->accept = $accept;
    }
    
    
    /**
     * @var bit
     *     
     * 
     * @Assert\NotBlank(message="Please confirm your age.", groups={"Registration", "FacebookRegistration"})         
     * 
     */
    protected $ageCheck;
    
    public function getAgeCheck()
    {
        return $this->ageCheck;
    }

    public function setAgeCheck($ageCheck)
    {
        $this->ageCheck = $ageCheck;
    }
    
    protected $newPassword;    
    public function getNewPassword()
    {
        return $this->newPassword;
    }
    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;
    }
    
    protected $repeatedPassword;    
    public function getRepeatedPassword()
    {
        return $this->repeatedPassword;
    }
    public function setRepeatedPassword($repeatedPassword)
    {
        $this->repeatedPassword = $repeatedPassword;
    }
    
      /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=10)
     */
    protected $phone;    
    public function getPhone()
    {
        return $this->phone;
    }
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
    
      /**
     * @var string
     *
     * @ORM\Column(name="phone_prefix", type="string", length=3)
     */
    protected $phonePrefix;    
    public function getPhonePrefix()
    {
        return $this->phonePrefix;
    }
    public function setPhonePrefix($phonePrefix)
    {
        $this->phonePrefix = $phonePrefix;
    }
    
    public function getFullPhone() {
        return trim(sprintf("%s %s", $this->phonePrefix, $this->phone));
    }
    
    
      /**
     * @var string
     *
     * @ORM\Column(name="iban", type="string", nullable=true)
     */
    protected $iban;    
    public function getIban()
    {
        return $this->iban;
    }
    public function setIban($iban)
    {
        $this->iban = $iban;
    }
    
      /**
     * @var string
     *
     * @ORM\Column(name="bic", type="string", nullable=true)
     */
    protected $bic;    
    public function getBic()
    {
        return $this->bic;
    }
    public function setBic($bic)
    {
        $this->bic = $bic;
    }
    
    
    
     /**
     * @var string
     *
     * @ORM\Column(name="about_myself", type="string", nullable=true, length=255)
      * 
     */
    protected $aboutMyself;    
    public function getAboutMyself()
    {
        return $this->aboutMyself;
    }
    public function setAboutMyself($aboutMyself)
    {
        $this->aboutMyself = $aboutMyself;
    }
    
    
    
    
    
    
    
    /**
     * @ORM\OneToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */
    protected $image;
    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return User
     */
    public function setImage(\AppBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }
    /**
     * Get image
     *
     * @return \AppBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }
    
    
    
    
    
    public function getFacebookPicture($large) {
        if ($this->facebookID === null) {
            return null;
        }
        return 'http://graph.facebook.com/'. $this->facebookID .'/picture' . ($large ? '?type=large' : '');
    }
    
    public function getProfilePicture($large, $imageUrlPrefix) {
        $imageUrl = "/img/placeholder/user-big.png"; // default
        if ($this->image != null) {            
            $imageUrl = $this->image->getUrlPath($imageUrlPrefix);            
        } 
        else if ($this->facebookID != null){
            $imageUrl = $this->getFacebookPicture($large);
        }         

        return $imageUrl;
    }
    public function getProfileThumbnailPicture($imageUrlPrefix) {
        $imageUrl = "/img/placeholder/user-thumb.png"; // default
        if ($this->image != null) {            
            $imageUrl = $this->image->getThumbnailUrlPath($imageUrlPrefix);            
        } 
        else if ($this->facebookID != null){
            $imageUrl = $this->getFacebookPicture(false);
        }         

        return $imageUrl;
    }
    
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
    protected $secondDayEmailSentAt;  
               
     /**
     * @ORM\Column(type="datetime")
     */
    protected $thirdDayEmailSentAt;  
    
    
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }
    
    public function setSecondDayEmailSentAt($secondDayEmailSentAt)
    {
        $this->secondDayEmailSentAt = $secondDayEmailSentAt;

        return $this;
    }

    public function getSecondDayEmailSentAt()
    {
        return $this->secondDayEmailSentAt;
    }
    
    public function setThirdDayEmailSentAt($thirdDayEmailSentAt)
    {
        $this->thirdDayEmailSentAt = $thirdDayEmailSentAt;

        return $this;
    }

    public function getThirdDayEmailSentAt()
    {
        return $this->thirdDayEmailSentAt;
    }
    
    /**
     * @ORM\OneToMany(targetEntity="DiscountCode", mappedBy="user")
     */
    protected $discountCodes;
    
    public function getDiscountCodes()
    {
        return $this->discountCodes;
    }
    
    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="user")
     */
    protected $equipments;
    /**
     * @ORM\OneToMany(targetEntity="Talent", mappedBy="user")
     */
    protected $talents;
    
    public function getEquipments()
    {
        return $this->equipments;
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->status = User::STATUS_OK;
        // your own logic
    }

    /**
     * @ORM\OneToMany(targetEntity="EquipmentInquiry", mappedBy="user")
     */
    protected $inquiries;
    

    /**
     * Add discountCode
     *
     * @param \AppBundle\Entity\DiscountCode $discountCode
     *
     * @return User
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
     * Add inquiry
     *
     * @param \AppBundle\Entity\EquipmentInquiry $inquiry
     *
     * @return User
     */
    public function addInquiry(\AppBundle\Entity\EquipmentInquiry $inquiry)
    {
        $this->inquiries[] = $inquiry;

        return $this;
    }

    /**
     * Remove inquiry
     *
     * @param \AppBundle\Entity\EquipmentInquiry $inquiry
     */
    public function removeInquiry(\AppBundle\Entity\EquipmentInquiry $inquiry)
    {
        $this->inquiries->removeElement($inquiry);
    }

    /**
     * Get inquiries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInquiries()
    {
        return $this->inquiries;
    }

    /**
     * Set rating
     *
     * @param string $rating
     *
     * @return User
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
     * Add equipment
     *
     * @param \AppBundle\Entity\Equipment $equipment
     *
     * @return User
     */
    public function addEquipment(\AppBundle\Entity\Equipment $equipment)
    {
        $this->equipments[] = $equipment;

        return $this;
    }

    /**
     * Remove equipment
     *
     * @param \AppBundle\Entity\Equipment $equipment
     */
    public function removeEquipment(\AppBundle\Entity\Equipment $equipment)
    {
        $this->equipments->removeElement($equipment);
    }

    /**
     * @ORM\OneToMany(targetEntity="UserRating", mappedBy="user")
     */
    protected $ratings;

    

    /**
     * Add rating
     *
     * @param \AppBundle\Entity\UserRating $rating
     *
     * @return User
     */
    public function addRating(\AppBundle\Entity\UserRating $rating)
    {
        $this->ratings[] = $rating;

        return $this;
    }

    /**
     * Remove rating
     *
     * @param \AppBundle\Entity\UserRating $rating
     */
    public function removeRating(\AppBundle\Entity\UserRating $rating)
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
     * Add talent
     *
     * @param \AppBundle\Entity\Talent $talent
     *
     * @return User
     */
    public function addTalent(\AppBundle\Entity\Talent $talent)
    {
        $this->talents[] = $talent;

        return $this;
    }

    /**
     * Remove talent
     *
     * @param \AppBundle\Entity\Talent $talent
     */
    public function removeTalent(\AppBundle\Entity\Talent $talent)
    {
        $this->talents->removeElement($talent);
    }

    /**
     * Get talents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTalents()
    {
        return $this->talents;
    }

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
     * Set addrStreet
     *
     * @param string $addrStreet
     *
     * @return User
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
     * @return User
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
     * Set addrFlatNumber
     *
     * @param string $addrFlatNumber
     *
     * @return User
     */
    public function setAddrFlatNumber($addrFlatNumber)
    {
        $this->addrFlatNumber = $addrFlatNumber;

        return $this;
    }

    /**
     * Get addrFlatNumber
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
     * @return User
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
     * @return User
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

    public function getFullName() {
        return $this->name . " " . $this->surname;
    }    
}
