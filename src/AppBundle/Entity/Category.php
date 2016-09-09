<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CategoryRepository")
 * @ORM\Table(name="category")
 */
class Category
{
    const TYPE_EQUIPMENT = 1;
    const TYPE_TALENT = 2;
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $type;
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
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @ORM\OneToMany(targetEntity="Subcategory", mappedBy="category")
     */
    protected $subcategories;
        
    /**
     * @ORM\OneToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */
    protected $image;
    /**
     * @ORM\OneToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="big_image_id", referencedColumnName="id")
     */
    protected $bigImage;
    
    /**
     * @ORM\Column(type="string", length=10000)
     */
    protected $descriptionMobile;

    /**
     * Set id
     *
     * @return Category
     * dummy for a skae of $form->handleRequest($request) becouse it looks for setId($id)
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
     * Constructor
     */
    public function __construct()
    {
        $this->subcategories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add subcategory
     *
     * @param \AppBundle\Entity\Subcategory $subcategory
     *
     * @return Category
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

    /**
     * Set image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return Category
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
     * Set bigImage
     *
     * @param \AppBundle\Entity\Image $bigImage
     *
     * @return Category
     */
    public function setBigImage(\AppBundle\Entity\Image $bigImage = null)
    {
        $this->bigImage = $bigImage;

        return $this;
    }

    /**
     * Get bigImage
     *
     * @return \AppBundle\Entity\Image
     */
    public function getBigImage()
    {
        return $this->bigImage;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Category
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
    
    public function getTypeStr()
    {
        if ($this->getType() === Category::TYPE_EQUIPMENT){
            return "Equipment";
        } else {
            return "Talent";
        }
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Category
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * Set name
     *
     * @param string $descriptionMobile
     *
     * @return Category
     */
    public function setdescriptionMobile($descriptionMobile)
    {
        $this->descriptionMobile = $descriptionMobile;

        return $this;
    }

    /**
     * Get descriptionMobile
     *
     * @return string
     */
    public function getDescriptionMobile()
    {
        return $this->descriptionMobile;
    }
}
