<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="BlogRepository")
 * @ORM\Table(name="blog")
 */
class Blog
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $title;  
    
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $slug;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;   
        
    /**
     * @ORM\Column(type="string")
     */
    protected $content;    
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;    

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

    public function setId($id)
    {
        return $this;
    }

    
    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
    
    public function getContent()
    {
        return $this->content;
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
    
    /**
     * @ORM\OneToMany(targetEntity="BlogRelated", mappedBy="blog")
     */
    protected $relatedBlogs;

    public function addRelatedBlog(Blog $blog)
    {
        $this->blog[] = $blog;

        return $this;
    }
    
    public function removeRelatedBlog(Blog $blog)
    {
        $this->relatedBlogs->removeElement($blog);
    }

    public function getRelatedBlogs()
    {
        return $this->relatedBlogs;
    }
    
    
    
    public function __construct()
    {
        
    }


    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     *
     * @return Blog
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
     * @return Blog
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Blog
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
     * Set bigImage
     *
     * @param \AppBundle\Entity\Image $bigImage
     *
     * @return Blog
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
}
