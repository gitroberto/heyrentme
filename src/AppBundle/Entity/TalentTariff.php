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
     * @ORM\Column(type="integer")
     */
    private $numDiscount;
    /**
     * @ORM\Column(type="decimal")
     */
    private $priceDiscount;
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
    //</editor-fold>

    // getters/setters
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
     * Set numDiscount
     *
     * @param integer $numDiscount
     *
     * @return TalentTariff
     */
    public function setNumDiscount($numDiscount)
    {
        $this->numDiscount = $numDiscount;

        return $this;
    }

    /**
     * Get numDiscount
     *
     * @return integer
     */
    public function getNumDiscount()
    {
        return $this->numDiscount;
    }

    /**
     * Set priceDiscount
     *
     * @param string $priceDiscount
     *
     * @return TalentTariff
     */
    public function setPriceDiscount($priceDiscount)
    {
        $this->priceDiscount = $priceDiscount;

        return $this;
    }

    /**
     * Get priceDiscount
     *
     * @return string
     */
    public function getPriceDiscount()
    {
        return $this->priceDiscount;
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
    //</editor-fold>

    
    public function getTypeName() {
        return TariffType::getByType($this->getType())->getName();
    }
    

}
