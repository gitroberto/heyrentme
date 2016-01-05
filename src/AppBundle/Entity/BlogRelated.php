<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="BlogRelatedRepository")
 * @ORM\Table(name="blog_related")
 */
class BlogRelated
{
    
    /**
    * @ORM\Column(type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $blogId;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $relatedBlogId;
    
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;   
        
    
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

    public function setBlogId($blogId)
    {
        $this->blogId = $blogId;
        return $this;
    }

    
    public function getBlogId()
    {
        return $this->blogId;
    }
    
     public function setRelatedBlogId($relatedBlogId)
    {
        $this->relatedBlogId = $relatedBlogId;
        return $this;
    }

    
    public function getRelatedBlogId()
    {
        return $this->relatedBlogId;
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
    
    
    
    
    public function __construct()
    {
        
    }

}
