<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SubcategoryRepository")
 * @ORM\Table(name="subcategory")
 */
class Subcategory
{
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
     * @ORM\Column(type="string", length=64)
     */
    protected $slug;
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="subcategories")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;    

    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="subcategory")
     */
    protected $equipments;
   
    /**
     * @ORM\OneToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */
    protected $image;
    
    /**
    * @ORM\OneToMany(targetEntity="FeatureSection", mappedBy="subcategory")
    */
    protected $featureSections;
    
    /** 
     * @ORM\Column(type="string", length=8192)
     */
    protected $emailBody;
    
    
    /**
     * Set id
     *
     * @return Subcategory
     * dummy for a sake of $form->handleRequest($request) becouse it looks for setId($id)
     */
    public function setId($id)
    {
        return $this;
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
     * Set name
     *
     * @param string $name
     *
     * @return Category
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Category
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Category
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
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Subcategory
     */
    public function setCategory(\AppBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->equipments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add equipment
     *
     * @param \AppBundle\Entity\Equipment $equipment
     *
     * @return Subcategory
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
     * Get equipments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipments()
    {
        return $this->equipments;
    }

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return Subcategory
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

    /**
     * Add featureSection
     *
     * @param \AppBundle\Entity\FeatureSection $featureSection
     *
     * @return Subcategory
     */
    public function addFeatureSection(\AppBundle\Entity\FeatureSection $featureSection)
    {
        $this->featureSections[] = $featureSection;

        return $this;
    }

    /**
     * Remove featureSection
     *
     * @param \AppBundle\Entity\FeatureSection $featureSection
     */
    public function removeFeatureSection(\AppBundle\Entity\FeatureSection $featureSection)
    {
        $this->featureSections->removeElement($featureSection);
    }

    /**
     * Get featureSections
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFeatureSections()
    {
        return $this->featureSections;
    }

    /**
     * Set emailBody
     *
     * @param string $emailBody
     *
     * @return Subcategory
     */
    public function setEmailBody($emailBody)
    {
        $this->emailBody = $emailBody;

        return $this;
    }

    /**
     * Get emailBody
     *
     * @return string
     */
    public function getEmailBody()
    {
        return $this->emailBody;
    }
}
