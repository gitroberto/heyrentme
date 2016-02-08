<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="TestimonialRepository")
 * @ORM\Table(name="testimonial")
 */
class Testimonial
{
    
    const TYPE_EQUIPMENT = 1;
    const TYPE_TALENT = 2;
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $type = Testimonial::TYPE_EQUIPMENT;  // default
    /**
     * @ORM\Column(type="integer")
     */
    protected $age;  
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $place;
    
    /**
     * @ORM\Column(type="string", length=500)
     */
    protected $description;
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;   
        
    /**
     * @ORM\OneToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */
    protected $image;
   

    public function setId($id)
    {
        return $this;
    }

    
    public function getId()
    {
        return $this->id;
    }

    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    public function getAge()
    {
        return $this->age;
    }
    
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }
    
    public function getPlace()
    {
        return $this->place;
    }
    
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
   
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition()
    {
        return $this->position;
    }
     public function setImage(Image $image = null)
    {
        $this->image = $image;

        return $this;
    }
    
    public function getImage()
    {
        return $this->image;
    }
   
    public function __construct()
    {
        
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Testimonial
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
}
