<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TalentTariffRepository")
 */
class TalentTariff {
    
    // fields
    //<editor-fold>
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;    
    /**
     * @ORM\ManyToOne(targetEntity="Talent", inversedBy="tariffs")
     * @ORM\JoinColumn(name="talent_id", referencedColumnName="id")
     */
    private $talent;    
    /**
     * @ORM\Column(type="decimal")
     */
    private $price;
    /**
     * @ORM\Column(type="integer")
     */
    private $type;
    /**
     * @ORM\Column(type="integer")
     */
    private $minNum;
    /**
     * @ORM\Column(type="boolean")
     */
    private $discount;
    /**
     * @ORM\Column(type="integer")
     */
    private $discountMinNum;
    /**
     * @ORM\Column(type="decimal")
     */
    private $discountPrice;
    /**
     * @ORM\Column(type="boolean")
     */
    private $ownPlace;
    /**
     * @ORM\Column(type="integer")
     */
    private $duration;
    /**
     * @ORM\Column(type="boolean")
     */
    private $requestPrice;
    /**
     * @ORM\Column(type="integer")
     */
    private $position;
    /**
     * @ORM\Column(type="boolean")
     */
    private $createdAt;
    /**
     * @ORM\Column(type="boolean")
     */
    private $modifiedAt;    
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
    //</editor-fold>

    public function getTariffType() {
        return TariffType::getByType($this->getType());
    }
    public function getTypeName() {
        return TariffType::getByType($this->getType())->getName();
    }
    public function getAddressAsString() {
        $fn = '';
        if (!empty($this->addrFlatNumber))
            $fn = '/' . $this->addrFlatNumber;
        return sprintf("%s %s%s, %s %s", $this->addrStreet, $this->addrNumber, $fn, $this->addrPostcode, $this->addrPlace);
    }
    public function getPricesLine() {
        switch ($this->type) {
            case TariffType::EINZELSTUNDEN:
            case TariffType::PERFORMANCE:
                return $this->requestPrice ? "auf Anfr." : (number_format($this->price, 2, ",", " ") . " &euro;");
            case TariffType::GRUPPENSTUNDEN:
            case TariffType::WORKSHOP:
            case TariffType::_5ERBLOCK:
            case TariffType::_10ERBLOCK:
            case TariffType::_20ERBLOCK:
            case TariffType::TAGESSATZ:
                return number_format($this->price, 2, ",", " ") . " &euro;";                
        }
    }
    public function getPricesDesc() {
        switch ($this->type) {
            case TariffType::EINZELSTUNDEN:
                return "pro Stunde";
            case TariffType::PERFORMANCE:
                return "Performance";
            case TariffType::GRUPPENSTUNDEN:
                return "Gruppenstd. / pro Per.";
            case TariffType::WORKSHOP:
                return "Workshop / pro Per.";
            case TariffType::_5ERBLOCK:
                return "5er Block";
            case TariffType::_10ERBLOCK:
                return "10er Block";
            case TariffType::_20ERBLOCK:
                return "20er Block";
            case TariffType::TAGESSATZ:
                return "pro Tag";
        }
    }
    
    // generated getters/setters
    //<editor-fold>    
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
     * Set price
     *
     * @param string $price
     *
     * @return TalentTariff
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
     * Set type
     *
     * @param integer $type
     *
     * @return TalentTariff
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set minNum
     *
     * @param integer $minNum
     *
     * @return TalentTariff
     */
    public function setMinNum($minNum)
    {
        $this->minNum = $minNum;

        return $this;
    }

    /**
     * Get minNum
     *
     * @return integer
     */
    public function getMinNum()
    {
        return $this->minNum;
    }

    /**
     * Set discount
     *
     * @param boolean $discount
     *
     * @return TalentTariff
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return boolean
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set discountMinNum
     *
     * @param integer $discountMinNum
     *
     * @return TalentTariff
     */
    public function setDiscountMinNum($discountMinNum)
    {
        $this->discountMinNum = $discountMinNum;

        return $this;
    }

    /**
     * Get discountMinNum
     *
     * @return integer
     */
    public function getDiscountMinNum()
    {
        return $this->discountMinNum;
    }

    /**
     * Set discountPrice
     *
     * @param string $discountPrice
     *
     * @return TalentTariff
     */
    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = $discountPrice;

        return $this;
    }

    /**
     * Get discountPrice
     *
     * @return string
     */
    public function getDiscountPrice()
    {
        return $this->discountPrice;
    }

    /**
     * Set ownPlace
     *
     * @param boolean $ownPlace
     *
     * @return TalentTariff
     */
    public function setOwnPlace($ownPlace)
    {
        $this->ownPlace = $ownPlace;

        return $this;
    }

    /**
     * Get ownPlace
     *
     * @return boolean
     */
    public function getOwnPlace()
    {
        return $this->ownPlace;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     *
     * @return TalentTariff
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set requestPrice
     *
     * @param boolean $requestPrice
     *
     * @return TalentTariff
     */
    public function setRequestPrice($requestPrice)
    {
        $this->requestPrice = $requestPrice;

        return $this;
    }

    /**
     * Get requestPrice
     *
     * @return boolean
     */
    public function getRequestPrice()
    {
        return $this->requestPrice;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return TalentTariff
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set createdAt
     *
     * @param boolean $createdAt
     *
     * @return TalentTariff
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return boolean
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @param boolean $modifiedAt
     *
     * @return TalentTariff
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @return boolean
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set talent
     *
     * @param \AppBundle\Entity\Talent $talent
     *
     * @return TalentTariff
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
     * Set addrStreet
     *
     * @param string $addrStreet
     *
     * @return TalentTariff
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
     * @return TalentTariff
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
     * @return TalentTariff
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
     * @return TalentTariff
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
     * @return TalentTariff
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
    //</editor-fold>
}