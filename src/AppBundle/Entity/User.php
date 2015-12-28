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
     * @var string
     *
     * @ORM\Column(name="Surname", type="string", nullable=true)
     * 
     *  *  * @Assert\NotBlank(message="Please enter your surname.", groups={"Registration", "Profile"})
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
     *  *  * @Assert\NotBlank(message="Please accept your surname.", groups={"Registration"})    
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
    
    
    
    
    public function getProfilePicture($large, $imageUrlPrefix)
    {
        $imageUrl = "/img/placeholder/user-big.png";
        if ($this->image != null) {            
            $imageUrl = $this->image->getUrlPath($imageUrlPrefix);            
        } else if ($this->facebookID != null){
            $imageUrl = 'http://graph.facebook.com/'. $this->facebookID .'/picture';
            if ($large){
                $imageUrl .= "?type=large";
            }
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
    
    
    /**
     * @ORM\OneToMany(targetEntity="DiscountCode", mappedBy="user")
     */
    protected $discountCodes;
    
    public function getDiscountCodes()
    {
        return $this->discountCodes;
    }
    
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @ORM\OneToMany(targetEntity="Inquiry", mappedBy="user")
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
     * @param \AppBundle\Entity\Inquiry $inquiry
     *
     * @return User
     */
    public function addInquiry(\AppBundle\Entity\Inquiry $inquiry)
    {
        $this->inquiries[] = $inquiry;

        return $this;
    }

    /**
     * Remove inquiry
     *
     * @param \AppBundle\Entity\Inquiry $inquiry
     */
    public function removeInquiry(\AppBundle\Entity\Inquiry $inquiry)
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
}
