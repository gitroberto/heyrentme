<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ImageRepository")
 * @ORM\Table(name="image")
 */
class Image
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $name;
    /**
     * @ORM\Column(type="string", length=36)
     */
    protected $uuid;
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $path;
    /**
     * @ORM\Column(type="string", length=4)
     */
    protected $extension;
    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $originalPath;
    
    public function getUrlPath($image_url_prefix) {
        return "{$image_url_prefix}{$this->getPath()}/{$this->getUuid()}.{$this->getExtension()}";
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
     * @return Image
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
     * Set uuid
     *
     * @param string $uuid
     *
     * @return Image
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return Image
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set extension
     *
     * @param string $extension
     *
     * @return Image
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set originalPath
     *
     * @param string $originalPath
     *
     * @return Image
     */
    public function setOriginalPath($originalPath)
    {
        $this->originalPath = $originalPath;

        return $this;
    }

    /**
     * Get originalPath
     *
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->originalPath;
    }
}
