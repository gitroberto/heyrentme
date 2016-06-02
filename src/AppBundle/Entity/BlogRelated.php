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
     * @ORM\ManyToOne(targetEntity="Blog")
     * @ORM\JoinColumn(name="related_blog_id", referencedColumnName="id")
     * @ORM\Id
     */
    protected $relatedBlog;
    
    /**
     * @ORM\ManyToOne(targetEntity="Blog")
     * @ORM\JoinColumn(name="blog_id", referencedColumnName="id")
     * @ORM\Id
     */
    protected $blog;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;   
        
    
    public function setBlog(\AppBundle\Entity\Blog $blog)
    {
        $this->blog = $blog;
        return $this;
    }

    
    public function getBlog()
    {
        return $this->blog;
    }
    
    public function setRelatedBlog(\AppBundle\Entity\Blog $relatedBlog)
    {
        $this->relatedBlog = $relatedBlog;
        return $this;
    }

    
    public function getRelatedBlog()
    {
        return $this->relatedBlog;
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
